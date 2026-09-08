<?php

declare(strict_types=1);

namespace App\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Audit\ValueObject\TauxConformite;

/**
 * Calcule le taux de conformité RGAA à partir des constats effectifs.
 *
 * Un critère sur lequel personne ne s'est prononcé n'existe pas en base : il n'y
 * a pas de constat à compter. Le laisser hors du calcul revient pourtant à
 * annoncer « 0 non testé » sur un audit qui n'a regardé qu'une partie du
 * référentiel, et à présenter comme complet ce qui ne l'est pas. Le calculateur
 * reçoit donc la liste des critères que le référentiel impose d'évaluer, et
 * compte comme non testé tout critère absent des constats.
 *
 * Le taux lui-même garde la définition officielle du RGAA, conformes sur
 * conformes plus non conformes : les non testés ne sont pas au dénominateur,
 * ils disent seulement que le taux ne porte pas encore sur tout le référentiel.
 */
final readonly class CalculateurTaux
{
    /**
     * @param list<Constat> $effectifs
     * @param list<string>  $criteresAttendus numéros de critères imposés par le référentiel
     *                                        du projet ; vide quand le référentiel n'a pas de
     *                                        liste fermée de critères à couvrir
     */
    public function calculer(array $effectifs, array $criteresAttendus = []): TauxConformite
    {
        $compteur = ['conformes' => 0, 'nonConformes' => 0, 'nonApplicables' => 0, 'nonTestes' => 0];
        $conformesParThematique = [];
        $totalParThematique = [];

        foreach ($effectifs as $constat) {
            $this->compter($compteur, $constat->statut());
            $this->ventiler($conformesParThematique, $totalParThematique, $constat);
        }

        $compteur['nonTestes'] += $this->criteresSansConstat($effectifs, $criteresAttendus);

        $evalues = $compteur['conformes'] + $compteur['nonConformes'];

        return new TauxConformite(
            $evalues > 0 ? $this->ratio($compteur['conformes'], $compteur['nonConformes']) : null,
            $this->tauxParThematique($conformesParThematique, $totalParThematique),
            $compteur['conformes'],
            $compteur['nonConformes'],
            $compteur['nonApplicables'],
            $compteur['nonTestes'],
        );
    }

    /**
     * Nombre de couples (unité auditée, critère) qu'aucun constat ne couvre.
     *
     * Les constats existants sont établis par unité auditée : le décompte suit
     * la même maille, sans quoi un référentiel non couvert pèserait autant sur
     * un échantillon d'une page que sur un échantillon de vingt.
     *
     * @param list<Constat> $effectifs
     * @param list<string>  $criteresAttendus
     */
    private function criteresSansConstat(array $effectifs, array $criteresAttendus): int
    {
        if ([] === $criteresAttendus || [] === $effectifs) {
            return 0;
        }

        $couvertsParUnite = [];
        foreach ($effectifs as $constat) {
            $couvertsParUnite[$constat->uniteAuditee()][$constat->critereNumero()] = true;
        }

        $manquants = 0;
        foreach ($couvertsParUnite as $couverts) {
            $manquants += count(array_diff($criteresAttendus, array_keys($couverts)));
        }

        return $manquants;
    }

    /**
     * @param array{conformes: int, nonConformes: int, nonApplicables: int, nonTestes: int} $compteur
     */
    private function compter(array &$compteur, StatutConformite $statut): void
    {
        $cle = match ($statut) {
            StatutConformite::Conforme => 'conformes',
            StatutConformite::NonConforme => 'nonConformes',
            StatutConformite::NonApplicable => 'nonApplicables',
            StatutConformite::NonTeste => 'nonTestes',
        };
        ++$compteur[$cle];
    }

    /**
     * @param array<int, int> $conformes
     * @param array<int, int> $total
     */
    private function ventiler(array &$conformes, array &$total, Constat $constat): void
    {
        if (!$constat->statut()->compteDansLeTaux()) {
            return;
        }

        $thematique = (int) explode('.', $constat->critereNumero())[0];
        $total[$thematique] = ($total[$thematique] ?? 0) + 1;

        if (StatutConformite::Conforme === $constat->statut()) {
            $conformes[$thematique] = ($conformes[$thematique] ?? 0) + 1;
        }
    }

    /**
     * @param array<int, int> $conformes
     * @param array<int, int> $total
     *
     * @return array<int, float>
     */
    private function tauxParThematique(array $conformes, array $total): array
    {
        $taux = [];
        foreach ($total as $thematique => $nb) {
            $taux[$thematique] = $this->ratio($conformes[$thematique] ?? 0, $nb - ($conformes[$thematique] ?? 0));
        }

        return $taux;
    }

    private function ratio(int $conformes, int $nonConformes): float
    {
        $total = $conformes + $nonConformes;

        return $total > 0 ? round($conformes / $total, 4) : 0.0;
    }
}
