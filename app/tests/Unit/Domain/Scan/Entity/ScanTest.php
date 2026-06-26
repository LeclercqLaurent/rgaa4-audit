<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scan\Entity;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\ScanResult;
use App\Domain\Scan\ValueObject\StatutScan;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ScanTest extends TestCase
{
    public function testUnScanDemarreEnAttente(): void
    {
        self::assertSame(StatutScan::EnAttente, $this->scan()->statut());
    }

    public function testTerminerCollecteLesResultatsEtHorodate(): void
    {
        $scan = $this->scan();
        $fin = new DateTimeImmutable('2026-02-02T10:00:00+00:00');

        $scan->demarrer();
        $scan->ajouterResultat(new ResultatPage('https://exemple.fr', new ScanResult('https://exemple.fr', '', [], [], [], [])));
        $scan->terminer($fin);

        self::assertSame(StatutScan::Termine, $scan->statut());
        self::assertSame($fin, $scan->dateFin());
        self::assertCount(1, $scan->resultats());
    }

    public function testEchouerConserveLaRaison(): void
    {
        $scan = $this->scan();

        $scan->demarrer();
        $scan->echouer('scanner indisponible', new DateTimeImmutable());

        self::assertSame(StatutScan::Echoue, $scan->statut());
        self::assertSame('scanner indisponible', $scan->erreur());
    }

    public function testReconstituerRestaureLEtatSansTransition(): void
    {
        $scan = Scan::reconstituer(
            'scan-1',
            'projet-1',
            new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            StatutScan::Termine,
            new DateTimeImmutable('2026-01-01T00:05:00+00:00'),
            null,
            [],
        );

        self::assertSame(StatutScan::Termine, $scan->statut());
        self::assertSame('projet-1', $scan->projetId());
    }

    private function scan(): Scan
    {
        return new Scan('scan-1', 'projet-1', new DateTimeImmutable('2026-01-01T00:00:00+00:00'));
    }
}
