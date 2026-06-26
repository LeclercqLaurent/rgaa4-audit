<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Taux de conformité RGAA d'un projet : conformes / (conformes + non conformes),
 * les critères non applicables et non testés étant exclus du dénominateur.
 *
 * `global` vaut `null` quand aucun critère n'est évaluable (à distinguer de 0 %).
 */
final readonly class TauxConformite
{
    /**
     * @param array<int, float> $parThematique numéro de thématique → taux (0..1)
     */
    public function __construct(
        public ?float $global,
        public array $parThematique,
        public int $conformes,
        public int $nonConformes,
        public int $nonApplicables,
        public int $nonTestes,
    ) {
    }
}
