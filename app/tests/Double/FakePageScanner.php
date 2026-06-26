<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\Scan\Port\PageScanner;
use App\Domain\Scan\ValueObject\NoeudAxe;
use App\Domain\Scan\ValueObject\ResultatAxe;
use App\Domain\Scan\ValueObject\ScanResult;

/**
 * PageScanner déterministe pour les tests : aucune dépendance à Node/Chromium.
 * Renvoie une violation `image-alt` (mappée WCAG 1.1.1) et un succès.
 */
final class FakePageScanner implements PageScanner
{
    public function scan(string $url): ScanResult
    {
        $violation = new ResultatAxe(
            'image-alt',
            'critical',
            'Images must have alternate text',
            'Images must have an alt attribute',
            'https://dequeuniversity.com/rules/axe/4.12/image-alt',
            ['wcag2a', 'wcag111'],
            [new NoeudAxe(['img'], '<img src="logo.png">', 'Fix: add an alt attribute')],
        );

        $passe = new ResultatAxe(
            'document-title',
            null,
            'Documents must have a title',
            'Documents must contain a title element',
            'https://dequeuniversity.com/rules/axe/4.12/document-title',
            ['wcag2a', 'wcag242'],
            [],
        );

        return new ScanResult($url, '2026-01-01T00:00:00.000Z', [$violation], [$passe], [], []);
    }
}
