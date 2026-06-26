<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\ValueObject;

use App\Domain\Audit\ValueObject\StatutConformite;
use PHPUnit\Framework\TestCase;

final class StatutConformiteTest extends TestCase
{
    public function testConformeEtNonConformeComptentDansLeTaux(): void
    {
        self::assertTrue(StatutConformite::Conforme->compteDansLeTaux());
        self::assertTrue(StatutConformite::NonConforme->compteDansLeTaux());
    }

    public function testNonApplicableEtNonTesteSontExclusDuTaux(): void
    {
        self::assertFalse(StatutConformite::NonApplicable->compteDansLeTaux());
        self::assertFalse(StatutConformite::NonTeste->compteDansLeTaux());
    }
}
