<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

/**
 * Déclenche l'analyse de complexité d'un projet de type « Complexité PHP »
 * (sur sa cible = chemin de code).
 */
final readonly class AnalyserComplexite
{
    public function __construct(public string $projetId)
    {
    }
}
