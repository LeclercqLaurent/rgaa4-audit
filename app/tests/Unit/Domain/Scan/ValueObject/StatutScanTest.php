<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Scan\ValueObject;

use App\Domain\Scan\ValueObject\StatutScan;
use PHPUnit\Framework\TestCase;

final class StatutScanTest extends TestCase
{
    public function testLesEtatsFinauxSontTermines(): void
    {
        self::assertTrue(StatutScan::Termine->estTermine());
        self::assertTrue(StatutScan::Echoue->estTermine());
    }

    public function testLesEtatsEnCoursNeSontPasTermines(): void
    {
        self::assertFalse(StatutScan::EnAttente->estTermine());
        self::assertFalse(StatutScan::EnCours->estTermine());
    }
}
