<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Audit;

use App\Application\Audit\Service\GenerateurConstatsAuto;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Scan\ValueObject\NoeudAxe;
use App\Domain\Scan\ValueObject\ResultatAxe;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\ScanResult;
use PHPUnit\Framework\TestCase;

final class GenerateurConstatsAutoTest extends TestCase
{
    public function testUneViolationRendLesCriteresMappesNonConformes(): void
    {
        $constats = $this->cartographier();

        self::assertSame(StatutConformite::NonConforme, $this->statut($constats, '1.1'));
        self::assertSame(StatutConformite::NonConforme, $this->statut($constats, '1.2'));
        self::assertSame(SourceConstat::Auto, $constats[0]->source());
    }

    public function testUnSuccesRendLesCriteresMappesConformes(): void
    {
        $constats = $this->cartographier();

        self::assertSame(StatutConformite::Conforme, $this->statut($constats, '8.5'));
    }

    public function testUneViolationAttacheSaPreuve(): void
    {
        $constats = $this->cartographier();
        $constat = $this->parCritere($constats, '1.1');

        self::assertCount(1, $constat->preuves());
        self::assertSame('image-alt', $constat->preuves()[0]->regle);
        self::assertSame('img', $constat->preuves()[0]->cible);
    }

    /**
     * @return list<Constat>
     */
    private function cartographier(): array
    {
        $violation = new ResultatAxe('image-alt', 'critical', '', '', 'https://help', ['wcag111'], [new NoeudAxe(['img'], '<img>', 'add alt')]);
        $passe = new ResultatAxe('document-title', null, '', '', 'https://help', ['wcag242'], []);
        $page = new ResultatPage('https://x', new ScanResult('https://x', '', [$violation], [$passe], [], []));

        $mapping = ['wcag111' => ['1.1', '1.2'], 'wcag242' => ['8.5']];

        return (new GenerateurConstatsAuto())->pour('projet-1', [$page], $mapping);
    }

    /**
     * @param list<Constat> $constats
     */
    private function statut(array $constats, string $critere): StatutConformite
    {
        return $this->parCritere($constats, $critere)->statut();
    }

    /**
     * @param list<Constat> $constats
     */
    private function parCritere(array $constats, string $critere): Constat
    {
        foreach ($constats as $constat) {
            if ($critere === $constat->critereNumero()) {
                return $constat;
            }
        }

        self::fail(sprintf('Aucun constat pour le critère %s.', $critere));
    }
}
