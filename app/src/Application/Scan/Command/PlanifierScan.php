<?php

declare(strict_types=1);

namespace App\Application\Scan\Command;

/**
 * Planifie un scan pour un projet : crée le scan en attente puis confie son
 * exécution au worker asynchrone.
 */
final readonly class PlanifierScan
{
    public function __construct(public string $projetId)
    {
    }
}
