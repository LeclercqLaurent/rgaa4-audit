<?php

declare(strict_types=1);

namespace App\Domain\Scan\Port;

use App\Domain\Scan\ValueObject\ScanResult;

/**
 * Analyse l'accessibilité d'une page et renvoie le résultat axe-core normalisé.
 *
 * L'implémentation concrète (Node/Puppeteer via Process, ou microservice HTTP)
 * vit en Infrastructure ; le Domain n'en connaît que ce contrat.
 */
interface PageScanner
{
    public function scan(string $url): ScanResult;
}
