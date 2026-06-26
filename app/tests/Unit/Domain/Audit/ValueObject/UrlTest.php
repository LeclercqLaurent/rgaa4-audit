<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\ValueObject;

use App\Domain\Audit\Exception\UrlInvalide;
use App\Domain\Audit\ValueObject\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function testAccepteUneUrlHttps(): void
    {
        $url = new Url('https://exemple.fr/contact');

        self::assertSame('https://exemple.fr/contact', (string) $url);
    }

    public function testNormaliseLesEspaces(): void
    {
        $url = new Url('  https://exemple.fr  ');

        self::assertSame('https://exemple.fr', $url->valeur);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function urlsInvalides(): iterable
    {
        yield 'vide' => [''];
        yield 'sans schéma' => ['exemple.fr'];
        yield 'schéma non http' => ['ftp://exemple.fr'];
        yield 'texte libre' => ['pas une url'];
    }

    #[DataProvider('urlsInvalides')]
    public function testRejetteUneUrlInvalide(string $valeur): void
    {
        $this->expectException(UrlInvalide::class);

        new Url($valeur);
    }
}
