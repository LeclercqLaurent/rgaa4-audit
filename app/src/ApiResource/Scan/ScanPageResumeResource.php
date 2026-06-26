<?php

declare(strict_types=1);

namespace App\ApiResource\Scan;

/**
 * Résumé chiffré du résultat axe-core d'une page (le détail est exploité côté
 * serveur pour produire les constats).
 */
final class ScanPageResumeResource
{
    public function __construct(
        public string $url,
        public int $violations,
        public int $passes,
        public int $incomplete,
        public int $inapplicable,
    ) {
    }
}
