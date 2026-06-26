<?php

declare(strict_types=1);

namespace App\Tests\Api\Scan;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Spécification d'acceptation du contexte Scan (Lot A2) : lancement d'un scan
 * sur l'échantillon d'un projet et suivi de son statut.
 *
 * Le PageScanner réel (Node) est remplacé par un double (cf. services_test.yaml)
 * et le transport Messenger est synchrone en test : le scan s'exécute en ligne.
 */
final class ScanApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purger();
    }

    public function testLancerUnScanProduitDesResultatsParPage(): void
    {
        $client = self::createClient();
        $projetId = $this->creerProjetAvecPage($client);

        $reponse = $client->request('POST', '/api/projets/'.$projetId.'/scans', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'projetId' => $projetId,
            'statut' => 'done',
            'pages' => [['url' => 'https://exemple.fr/contact', 'violations' => 1, 'passes' => 1]],
        ]);

        /** @var array{id: string} $scan */
        $scan = $reponse->toArray();
        $client->request('GET', '/api/scans/'.$scan['id']);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['statut' => 'done']);
    }

    public function testScannerUnProjetInconnuRenvoie404(): void
    {
        self::createClient()->request('POST', '/api/projets/019f0000-0000-7000-8000-000000000000/scans', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjetAvecPage(object $client): string
    {
        $reponse = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet scan', 'client' => 'Client', 'urlReference' => 'https://exemple.fr'],
        ]);
        /** @var array{id: string} $data */
        $data = $reponse->toArray();
        $client->request('POST', '/api/projets/'.$data['id'].'/pages', [
            'json' => ['url' => 'https://exemple.fr/contact', 'titre' => 'Contact'],
        ]);

        return $data['id'];
    }

    private function purger(): void
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['scan', 'audit_page', 'audit_projet'] as $table) {
            $connection->executeStatement('TRUNCATE TABLE '.$table);
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
