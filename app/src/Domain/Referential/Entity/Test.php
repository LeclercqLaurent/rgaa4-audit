<?php

declare(strict_types=1);

namespace App\Domain\Referential\Entity;

/**
 * Test d'un critère RGAA (ex. 1.1.1). Un énoncé peut comporter plusieurs lignes
 * (conditions à vérifier).
 */
final readonly class Test
{
    /**
     * @param list<string> $enonces
     */
    public function __construct(
        public string $numero,
        public array $enonces,
    ) {
    }
}
