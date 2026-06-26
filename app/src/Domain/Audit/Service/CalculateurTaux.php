<?php

declare(strict_types=1);

namespace App\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Audit\ValueObject\TauxConformite;

/**
 * Calcule le taux de conformité RGAA à partir des constats effectifs.
 */
final readonly class CalculateurTaux
{
    /**
     * @param list<Constat> $effectifs
     */
    public function calculer(array $effectifs): TauxConformite
    {
        $compteur = ['conformes' => 0, 'nonConformes' => 0, 'nonApplicables' => 0, 'nonTestes' => 0];
        $conformesParThematique = [];
        $totalParThematique = [];

        foreach ($effectifs as $constat) {
            $this->compter($compteur, $constat->statut());
            $this->ventiler($conformesParThematique, $totalParThematique, $constat);
        }

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
