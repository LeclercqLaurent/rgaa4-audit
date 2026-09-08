<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\ValueObject\Referentiel;
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

    public function testUnCritereAttenduSansConstatEstCompteNonTeste(): void
    {
        $taux = (new CalculateurTaux())->calculer(
            [$this->constat('1.1', StatutConformite::Conforme)],
            ['1.1', '1.2', '1.3'],
        );

        self::assertSame(2, $taux->nonTestes);
    }

    public function testLeDecompteDesNonTestesSuitLesUnitesAuditees(): void
    {
        $taux = (new CalculateurTaux())->calculer(
            [
                $this->constat('1.1', StatutConformite::Conforme, 'https://exemple.fr/a'),
                $this->constat('1.1', StatutConformite::Conforme, 'https://exemple.fr/b'),
            ],
            ['1.1', '1.2', '1.3'],
        );

        // Deux critères manquent sur chacune des deux pages de l'échantillon.
        self::assertSame(4, $taux->nonTestes);
    }

    public function testLesNonTestesNePesentPasSurLeTaux(): void
    {
        $constats = [
            $this->constat('1.1', StatutConformite::Conforme),
            $this->constat('1.2', StatutConformite::NonConforme),
        ];

        $sansAttendus = (new CalculateurTaux())->calculer($constats);
        $avecAttendus = (new CalculateurTaux())->calculer($constats, ['1.1', '1.2', '1.3', '1.4']);

        self::assertSame(0.5, $sansAttendus->global);
        self::assertSame(0.5, $avecAttendus->global, 'Le taux garde la formule officielle du RGAA.');
        self::assertSame(0, $sansAttendus->nonTestes);
        self::assertSame(2, $avecAttendus->nonTestes);
    }

    public function testUnReferentielSansListeFermeeNeCompteAucunManquant(): void
    {
        $taux = (new CalculateurTaux())->calculer([$this->constat('cognitive', StatutConformite::NonConforme)], []);

        self::assertSame(0, $taux->nonTestes);
    }

    public function testAucunConstatNeProduitAucunManquant(): void
    {
        // Un projet sans le moindre scan n'échoue pas sur 106 critères : il n'a
        // simplement pas commencé.
        $taux = (new CalculateurTaux())->calculer([], ['1.1', '1.2']);

        self::assertSame(0, $taux->nonTestes);
        self::assertNull($taux->global);
    }

    private function constat(string $critere, StatutConformite $statut, string $unite = 'https://exemple.fr'): Constat
    {
        return new Constat('projet-1', Referentiel::Rgaa, $unite, $critere, $statut, SourceConstat::Auto);
    }
}
