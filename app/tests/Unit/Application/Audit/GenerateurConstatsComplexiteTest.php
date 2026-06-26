<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Audit;

use App\Application\Audit\Service\GenerateurConstatsComplexite;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Scan\ValueObject\MethodeComplexite;
use App\Domain\Scan\ValueObject\ResultatComplexite;
use PHPUnit\Framework\TestCase;

final class GenerateurConstatsComplexiteTest extends TestCase
{
    public function testUneMesureSousLeSeuilEstConformeAuDelaNonConforme(): void
    {
        $resultat = new ResultatComplexite(
            [
                new MethodeComplexite('A.php', 'foo', 10, ['cognitive' => 20, 'params' => 2]),
                new MethodeComplexite('B.php', 'bar', 5, ['cognitive' => 5, 'params' => 2]),
            ],
            ['cognitive' => 15, 'params' => 7],
        );

        $constats = (new GenerateurConstatsComplexite())->pour('projet-1', $resultat);

        // 2 méthodes × 2 lentilles
        self::assertCount(4, $constats);
        // cognitive = 20 > 15 → non conforme, sur le critère court « cog »
        self::assertSame(StatutConformite::NonConforme, $this->statut($constats, 'A.php::foo (l.10)', 'cog'));
        self::assertSame(StatutConformite::Conforme, $this->statut($constats, 'A.php::foo (l.10)', 'par'));
        self::assertSame(StatutConformite::Conforme, $this->statut($constats, 'B.php::bar (l.5)', 'cog'));
    }

    /**
     * @param list<Constat> $constats
     */
    private function statut(array $constats, string $page, string $critere): StatutConformite
    {
        foreach ($constats as $constat) {
            if ($constat->pageUrl() === $page && $constat->critereNumero() === $critere) {
                return $constat->statut();
            }
        }

        self::fail(sprintf('Constat introuvable : %s / %s.', $page, $critere));
    }
}
