<?php

declare(strict_types=1);

namespace App\Domain\Reporting\ValueObject;

/**
 * Regroupement des lignes du rapport par thématique RGAA.
 */
final readonly class SectionThematique
{
    /**
     * @param list<LigneRapport> $lignes
     */
    public function __construct(
        public int $numero,
        public string $nom,
        public array $lignes,
        public ?float $taux,
    ) {
    }
}
