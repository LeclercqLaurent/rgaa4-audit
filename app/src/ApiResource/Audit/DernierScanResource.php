<?php

declare(strict_types=1);

namespace App\ApiResource\Audit;

/**
 * Résumé du dernier scan d'un projet, embarqué dans ProjetResource.
 */
final class DernierScanResource
{
    public function __construct(
        public string $statut,
        public string $date,
    ) {
    }
}
