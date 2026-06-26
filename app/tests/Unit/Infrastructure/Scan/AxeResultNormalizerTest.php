<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Scan;

use App\Infrastructure\Scan\Adapter\AxeResultNormalizer;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class AxeResultNormalizerTest extends TestCase
{
    public function testNormaliseLeJsonAxe(): void
    {
        $json = json_encode([
            'url' => 'https://exemple.fr',
            'timestamp' => '2026-01-01T00:00:00.000Z',
            'violations' => [[
                'id' => 'image-alt',
                'impact' => 'critical',
                'description' => 'Images must have alternate text',
                'help' => 'Images must have an alt attribute',
                'helpUrl' => 'https://example/rules/image-alt',
                'tags' => ['wcag2a', 'wcag111'],
                'nodes' => [['target' => ['img'], 'html' => '<img>', 'failureSummary' => 'add alt']],
            ]],
            'passes' => [],
            'incomplete' => [],
            'inapplicable' => [],
        ], JSON_THROW_ON_ERROR);

        $resultat = (new AxeResultNormalizer())->fromJson($json);

        self::assertSame('https://exemple.fr', $resultat->url);
        self::assertCount(1, $resultat->violations);
        self::assertSame('image-alt', $resultat->violations[0]->id);
        self::assertSame(['wcag2a', 'wcag111'], $resultat->violations[0]->tags);
        self::assertSame(['img'], $resultat->violations[0]->noeuds[0]->cibles);
    }

    public function testRejetteUnJsonNonObjet(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new AxeResultNormalizer())->fromJson('"texte"');
    }
}
