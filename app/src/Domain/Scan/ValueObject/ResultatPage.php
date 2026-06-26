<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Rattache le résultat axe-core d'une page à son URL au sein d'un scan.
 */
final readonly class ResultatPage
{
    public function __construct(
        public string $url,
        public ScanResult $resultat,
    ) {
    }
}
