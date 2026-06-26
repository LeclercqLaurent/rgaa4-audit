<?php

declare(strict_types=1);

namespace App\Domain\Scan\Port;

use App\Domain\Scan\ValueObject\ResultatComplexite;

/**
 * Analyse la complexité d'un dossier de code source (2ᵉ moteur d'audit).
 * L'implémentation invoque phpx-complexity ; le Domain ne connaît que ce contrat.
 */
interface AnalyseurCode
{
    public function analyser(string $chemin): ResultatComplexite;
}
