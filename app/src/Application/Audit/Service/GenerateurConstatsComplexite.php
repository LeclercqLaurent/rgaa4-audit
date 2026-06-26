<?php

declare(strict_types=1);

namespace App\Application\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Scan\ValueObject\ResultatComplexite;

/**
 * 2ᵉ moteur : transforme un résultat phpx-complexity en constats par
 * (méthode, lentille) — conforme si la mesure est sous le seuil, sinon non
 * conforme.
 *
 * Écart Phase B : `critere_numero` étant en VARCHAR(8) (codes RGAA), on mappe
 * les lentilles vers des codes courts (cognitive→cog, live_peak→liv, …).
 */
final readonly class GenerateurConstatsComplexite
{
    private const CODES = [
        'cognitive' => 'cog',
        'params' => 'par',
        'returns' => 'ret',
        'live_peak' => 'liv',
        'entangle' => 'ent',
    ];

    /**
     * @return list<Constat>
     */
    public function pour(string $projetId, ResultatComplexite $resultat): array
    {
        $constats = [];
        foreach ($resultat->methodes as $methode) {
            foreach ($resultat->seuils as $lentille => $seuil) {
                $valeur = $methode->mesures[$lentille] ?? 0;
                $depasse = $valeur > $seuil;

                $constats[] = new Constat(
                    $projetId,
                    $methode->reference(),
                    self::CODES[$lentille] ?? substr($lentille, 0, 8),
                    $depasse ? StatutConformite::NonConforme : StatutConformite::Conforme,
                    SourceConstat::Auto,
                    $depasse ? [$this->preuve($lentille, $methode->reference(), $valeur, $seuil)] : [],
                );
            }
        }

        return $constats;
    }

    private function preuve(string $lentille, string $cible, int $valeur, int $seuil): Preuve
    {
        return new Preuve($lentille, null, $cible, '', sprintf('%s = %d (seuil %d)', $lentille, $valeur, $seuil), '');
    }
}
