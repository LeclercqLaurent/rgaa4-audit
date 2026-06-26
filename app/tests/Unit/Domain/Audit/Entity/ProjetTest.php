<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\Entity;

use App\Domain\Audit\Entity\Page;
use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\ValueObject\Url;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProjetTest extends TestCase
{
    public function testUnNouveauProjetNaPasDePage(): void
    {
        self::assertSame([], $this->projet()->pages());
    }

    public function testAjouterPageEnrichitLEchantillon(): void
    {
        $projet = $this->projet();

        $projet->ajouterPage(new Page('page-1', new Url('https://exemple.fr/a'), 'Accueil'));
        $projet->ajouterPage(new Page('page-2', new Url('https://exemple.fr/b'), 'Contact'));

        self::assertCount(2, $projet->pages());
        self::assertSame('Accueil', $projet->pages()[0]->titre);
        self::assertSame('https://exemple.fr/b', (string) $projet->pages()[1]->url);
    }

    private function projet(): Projet
    {
        return new Projet(
            'projet-1',
            'Audit démo',
            'Client démo',
            new Url('https://exemple.fr'),
            new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );
    }
}
