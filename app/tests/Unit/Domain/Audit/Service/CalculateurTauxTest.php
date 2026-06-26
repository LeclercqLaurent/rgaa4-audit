<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use PHPUnit\Framework\TestCase;

final class CalculateurTauxTest extends TestCase
{
    public function testTauxGlobalExcluantNonApplicablesEtNonTestes(): void
    {
        $taux = (new CalculateurTaux())->calculer([
            $this->constat('1.1', StatutConformite::Conforme),
            $this->constat('1.2', StatutConformite::NonConforme),
            $this->constat('2.1', StatutConformite::NonApplicable),
            $this->constat('3.1', StatutConformite::NonTeste),
        ]);

        self::assertSame(0.5, $taux->global);
        self::assertSame(1, $taux->conformes);
        self::assertSame(1, $taux->nonConformes);
        self::assertSame(1, $taux->nonApplicables);
        self::assertSame(1, $taux->nonTestes);
    }

    public function testTauxParThematique(): void
    {
        $taux = (new CalculateurTaux())->calculer([
            $this->constat('1.1', StatutConformite::Conforme),
            $this->constat('1.2', StatutConformite::Conforme),
            $this->constat('1.3', StatutConformite::NonConforme),
            $this->constat('2.1', StatutConformite::Conforme),
        ]);

        self::assertEqualsWithDelta(0.6667, $taux->parThematique[1], 0.0001);
        self::assertSame(1.0, $taux->parThematique[2]);
    }

    public function testTauxNulSansCritereEvaluable(): void
    {
        $taux = (new CalculateurTaux())->calculer([$this->constat('1.1', StatutConformite::NonTeste)]);

        self::assertNull($taux->global);
        self::assertSame([], $taux->parThematique);
    }

    private function constat(string $critere, StatutConformite $statut): Constat
    {
        return new Constat('projet-1', 'https://exemple.fr', $critere, $statut, SourceConstat::Auto);
    }
}
