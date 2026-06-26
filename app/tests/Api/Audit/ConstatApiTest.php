<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Spécification d'acceptation du Lot A3 : un scan terminé alimente les constats
 * de conformité RGAA (mapping axe → WCAG → RGAA), avec preuves.
 *
 * Le PageScanner est mocké (violation image-alt → tag wcag111) et le transport
 * Messenger est synchrone en test, donc les constats sont générés en ligne.
 */
final class ConstatApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purger();
    }

    public function testUnScanAlimenteLesConstatsRgaa(): void
    {
        $client = self::createClient();
        $projetId = $this->creerProjetAvecPage($client);

        $client->request('POST', '/api/projets/'.$projetId.'/scans', ['json' => (object) []]);
        $this->assertResponseStatusCodeSame(201);

        $reponse = $client->request('GET', '/api/projets/'.$projetId.'/constats');
        $this->assertResponseIsSuccessful();

        /** @var array<int, array{critereNumero: string, statut: string, source: string}> $constats */
        $constats = $reponse->toArray();
        $parCritere = [];
        foreach ($constats as $constat) {
            $parCritere[$constat['critereNumero']] = $constat;
        }

        // wcag111 (image-alt) est mappé au critère RGAA 1.1 → non conforme, source auto.
        self::assertArrayHasKey('1.1', $parCritere);
        self::assertSame('non_conforme', $parCritere['1.1']['statut']);
        self::assertSame('auto', $parCritere['1.1']['source']);
    }

    private function creerProjetAvecPage(object $client): string
    {
        $reponse = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet constats', 'client' => 'Client', 'urlReference' => 'https://exemple.fr'],
        ]);
        /** @var array{id: string} $data */
        $data = $reponse->toArray();
        $client->request('POST', '/api/projets/'.$data['id'].'/pages', [
            'json' => ['url' => 'https://exemple.fr/accueil', 'titre' => 'Accueil'],
        ]);

        return $data['id'];
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
