<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Résultat axe-core normalisé pour une page : les quatre catégories axe-core.
 */
final readonly class ScanResult
{
    /**
     * @param list<ResultatAxe> $violations
     * @param list<ResultatAxe> $passes
     * @param list<ResultatAxe> $incomplete
     * @param list<ResultatAxe> $inapplicable
     */
    public function __construct(
        public string $url,
        public string $timestamp,
        public array $violations,
        public array $passes,
        public array $incomplete,
        public array $inapplicable,
    ) {
    }
}
