<?php

declare(strict_types=1);

namespace App\Application\Reporting\Query;

use App\Application\Audit\Query\ListerConstats;
use App\Application\Audit\Query\ListerConstatsHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Application\Audit\Service\CriteresAttendus;
use App\Application\Referential\Query\ListerThematiques;
use App\Application\Referential\Query\ListerThematiquesHandler;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\Service\ResolveurConstatsEffectifs;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\TauxConformite;
use App\Domain\Referential\Entity\Thematique;
use App\Domain\Reporting\ValueObject\LigneRapport;
use App\Domain\Reporting\ValueObject\Rapport;
use App\Domain\Reporting\ValueObject\SectionThematique;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Assemble le rapport d'audit : projet + taux + sections par thématique
 * (constats effectifs enrichis des intitulés du référentiel).
 */
final readonly class ObtenirRapportHandler
{
    /**
     * Libellés des lentilles de complexité (sections du rapport « Complexité PHP »).
     */
    private const LENTILLES = [
        'cognitive' => 'Complexité cognitive (S3776)',
        'params' => 'Nombre de paramètres (S107)',
        'returns' => 'Points de sortie (S1142)',
        'live_peak' => 'Pic de variables vivantes',
        'entangle' => 'Intrication des variables',
    ];

    public function __construct(
        private ObtenirProjetHandler $obtenirProjet,
        private ListerConstatsHandler $listerConstats,
        private ListerThematiquesHandler $listerThematiques,
        private ResolveurConstatsEffectifs $resolveur,
        private CalculateurTaux $calculateur,
        private CriteresAttendus $criteresAttendus,
    ) {
    }

    public function __invoke(ObtenirRapport $query): Rapport
    {
        $projet = ($this->obtenirProjet)(new ObtenirProjet($query->projetId));

        if (null === $projet) {
            throw ProjetIntrouvable::pour($query->projetId);
        }

        $effectifs = $this->resolveur->resoudre(($this->listerConstats)(new ListerConstats($query->projetId)));
        $taux = $this->calculateur->calculer($effectifs, $this->criteresAttendus->pour($projet->type()));
        $thematiques = ($this->listerThematiques)(new ListerThematiques());

        return new Rapport(
            $projet->nom(),
            $projet->client(),
            $projet->type()->value,
            $projet->cible(),
            (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            $taux,
            $this->sections($projet->type(), $thematiques, $effectifs, $taux),
        );
    }

    /**
     * Sections du rapport selon le référentiel du projet : par thématique (RGAA)
     * ou par lentille de complexité.
     *
     * @param list<Thematique> $thematiques
     * @param list<Constat>    $effectifs
     *
     * @return list<SectionThematique>
     */
    private function sections(Referentiel $type, array $thematiques, array $effectifs, TauxConformite $taux): array
    {
        return Referentiel::ComplexitePhp === $type
            ? $this->sectionsComplexite($effectifs)
            : $this->sectionsRgaa($thematiques, $effectifs, $taux);
    }

    /**
     * @param list<Thematique> $thematiques
     * @param list<Constat>    $effectifs
     *
     * @return list<SectionThematique>
     */
    private function sectionsRgaa(array $thematiques, array $effectifs, TauxConformite $taux): array
    {
        $intitules = $this->intitules($thematiques);
        $lignesParThematique = $this->lignesParThematique($effectifs, $intitules);

        $sections = [];
        foreach ($thematiques as $thematique) {
            $lignes = $lignesParThematique[$thematique->numero] ?? [];
            if ([] !== $lignes) {
                $sections[] = new SectionThematique($thematique->numero, $thematique->nom, $lignes, $taux->parThematique[$thematique->numero] ?? null);
            }
        }

        return $sections;
    }

    /**
     * @param list<Constat> $effectifs
     *
     * @return list<SectionThematique>
     */
    private function sectionsComplexite(array $effectifs): array
    {
        $parLentille = [];
        foreach ($effectifs as $constat) {
            $parLentille[$constat->critereNumero()][] = $constat;
        }

        $sections = [];
        $numero = 1;
        foreach (self::LENTILLES as $cle => $label) {
            $constats = $parLentille[$cle] ?? [];
            if ([] === $constats) {
                continue;
            }

            $lignes = array_map(
                static fn (Constat $c): LigneRapport => new LigneRapport($c->critereNumero(), $label, $c->uniteAuditee(), $c->statut(), $c->source(), $c->preuves()),
                $constats,
            );
            // Les lentilles de complexité n'ont pas de liste fermée de critères :
            // rien n'y manque par construction, donc aucun critère attendu à opposer.
            $sections[] = new SectionThematique($numero++, $label, $lignes, $this->calculateur->calculer($constats, [])->global);
        }

        return $sections;
    }

    /**
     * @param list<Thematique> $thematiques
     *
     * @return array<string, array{intitule: string, thematique: int}>
     */
    private function intitules(array $thematiques): array
    {
        $catalogue = [];
        foreach ($thematiques as $thematique) {
            foreach ($thematique->criteres as $critere) {
                $catalogue[$critere->numero] = ['intitule' => $critere->intitule, 'thematique' => $thematique->numero];
            }
        }

        return $catalogue;
    }

    /**
     * @param list<Constat>                                                $effectifs
     * @param array<string, array{intitule: string, thematique: int}>      $intitules
     *
     * @return array<int, list<LigneRapport>>
     */
    private function lignesParThematique(array $effectifs, array $intitules): array
    {
        $lignes = [];
        foreach ($effectifs as $constat) {
            $info = $intitules[$constat->critereNumero()] ?? ['intitule' => '', 'thematique' => 0];
            $lignes[$info['thematique']][] = new LigneRapport(
                $constat->critereNumero(),
                $info['intitule'],
                $constat->uniteAuditee(),
                $constat->statut(),
                $constat->source(),
                $constat->preuves(),
            );
        }

        return $lignes;
    }
}
