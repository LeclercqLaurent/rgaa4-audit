<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

/**
 * Déclenche l'audit d'un projet via le moteur de son référentiel (point d'entrée
 * unifié, indépendant du type d'audit).
 */
final readonly class LancerAudit
{
    public function __construct(public string $projetId)
    {
    }
}
