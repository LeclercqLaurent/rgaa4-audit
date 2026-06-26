<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Service\ResolveurConstatsEffectifs;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use PHPUnit\Framework\TestCase;

final class ResolveurConstatsEffectifsTest extends TestCase
{
    public function testLeConstatManuelPrimeSurLAuto(): void
    {
        $effectifs = (new ResolveurConstatsEffectifs())->resoudre([
            new Constat('p', 'https://x', '1.1', StatutConformite::Conforme, SourceConstat::Auto),
            new Constat('p', 'https://x', '1.1', StatutConformite::NonConforme, SourceConstat::Manuel),
            new Constat('p', 'https://x', '1.2', StatutConformite::Conforme, SourceConstat::Auto),
        ]);

        self::assertCount(2, $effectifs);
        $parCritere = [];
        foreach ($effectifs as $constat) {
            $parCritere[$constat->critereNumero()] = $constat;
        }
        self::assertSame(StatutConformite::NonConforme, $parCritere['1.1']->statut());
        self::assertSame(SourceConstat::Manuel, $parCritere['1.1']->source());
        self::assertSame(SourceConstat::Auto, $parCritere['1.2']->source());
    }
}
