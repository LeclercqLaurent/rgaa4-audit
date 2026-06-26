<?php

declare(strict_types=1);

namespace App\Application\Reporting\Query;

use App\Application\Audit\Query\ListerConstats;
use App\Application\Audit\Query\ListerConstatsHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Application\Referential\Query\ListerThematiques;
use App\Application\Referential\Query\ListerThematiquesHandler;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\Service\ResolveurConstatsEffectifs;
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
    public function __construct(
        private ObtenirProjetHandler $obtenirProjet,
        private ListerConstatsHandler $listerConstats,
        private ListerThematiquesHandler $listerThematiques,
        private ResolveurConstatsEffectifs $resolveur,
        private CalculateurTaux $calculateur,
    ) {
    }

    public function __invoke(ObtenirRapport $query): Rapport
    {
        $projet = ($this->obtenirProjet)(new ObtenirProjet($query->projetId));

        if (null === $projet) {
            throw ProjetIntrouvable::pour($query->projetId);
        }

        $effectifs = $this->resolveur->resoudre(($this->listerConstats)(new ListerConstats($query->projetId)));
        $taux = $this->calculateur->calculer($effectifs);
        $thematiques = ($this->listerThematiques)(new ListerThematiques());

        return new Rapport(
            $projet->nom(),
            $projet->client(),
            $projet->cible(),
            (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            $taux,
            $this->sections($thematiques, $effectifs, $taux),
        );
    }

    /**
     * @param list<Thematique>      $thematiques
     * @param list<Constat>         $effectifs
     *
     * @return list<SectionThematique>
     */
    private function sections(array $thematiques, array $effectifs, TauxConformite $taux): array
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
                $constat->pageUrl(),
                $constat->statut(),
                $constat->source(),
                $constat->preuves(),
            );
        }

        return $lignes;
    }
}
