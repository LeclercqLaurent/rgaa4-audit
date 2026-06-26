<?php

declare(strict_types=1);

namespace App\Domain\Referential\Entity;

/**
 * Thématique RGAA (ex. 1 « Images »). Racine de regroupement des critères.
 */
final readonly class Thematique
{
    /**
     * @param list<Critere> $criteres
     */
    public function __construct(
        public int $numero,
        public string $nom,
        public array $criteres,
    ) {
    }
}
