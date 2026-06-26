<?php

declare(strict_types=1);

namespace App\Tests\Api\Reporting;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Spécification d'acceptation du Lot A5 : génération du rapport d'audit RGAA
 * (HTML et PDF), avec taux, détail des constats et plan d'action.
 */
final class RapportApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purger();
    }

    public function testRapportHtmlContientLeTauxEtLePlanAction(): void
    {
        $projetId = $this->creerProjetScanne(self::createClient());

        $reponse = self::createClient()->request('GET', '/api/projets/'.$projetId.'/rapport');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');
        $html = $reponse->getContent();
        self::assertStringContainsString('Rapport d\'audit d\'accessibilité RGAA 4', $html);
        self::assertStringContainsString('Taux de conformité', $html);
        self::assertStringContainsString('Plan d\'action', $html);
    }

    public function testRapportPdf(): void
    {
        $projetId = $this->creerProjetScanne(self::createClient());

        $reponse = self::createClient()->request('GET', '/api/projets/'.$projetId.'/rapport.pdf');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', $reponse->getContent());
    }

    public function testRapportProjetInconnuRenvoie404(): void
    {
        self::createClient()->request('GET', '/api/projets/019f0000-0000-7000-8000-000000000000/rapport');

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjetScanne(object $client): string
    {
        $data = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet rapport', 'client' => 'Client', 'urlReference' => 'https://exemple.fr'],
        ])->toArray();
        $projetId = is_string($data['id']) ? $data['id'] : '';
        $client->request('POST', '/api/projets/'.$projetId.'/pages', ['json' => ['url' => 'https://exemple.fr/accueil', 'titre' => 'Accueil']]);
        $client->request('POST', '/api/projets/'.$projetId.'/scans', ['json' => (object) []]);

        return $projetId;
    }

    private function purger(): void
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['audit_constat', 'scan', 'audit_page', 'audit_projet'] as $table) {
            $connection->executeStatement('TRUNCATE TABLE '.$table);
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
