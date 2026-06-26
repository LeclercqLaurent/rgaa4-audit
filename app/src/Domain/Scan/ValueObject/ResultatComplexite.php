<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Résultat normalisé d'une analyse de complexité PHP (phpx-complexity).
 */
final readonly class ResultatComplexite
{
    /**
     * @param list<MethodeComplexite> $methodes
     * @param array<string, int>      $seuils   clé de lentille → seuil
     */
    public function __construct(
        public array $methodes,
        public array $seuils,
    ) {
    }
}
