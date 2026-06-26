<?php

declare(strict_types=1);

namespace App\Tests\Api\Scan;

use App\Tests\Api\ApiSecuriseeTestCase;

/**
 * Spécification d'acceptation du contexte Scan (Lot A2) : lancement d'un scan
 * sur l'échantillon d'un projet et suivi de son statut.
 *
 * Le PageScanner réel (Node) est remplacé par un double (cf. services_test.yaml)
 * et le transport Messenger est synchrone en test : le scan s'exécute en ligne.
 */
final class ScanApiTest extends ApiSecuriseeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purgerAudit();
    }

    public function testLancerUnScanProduitDesResultatsParPage(): void
    {
        $client = $this->clientAuthentifie();
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
        $this->clientAuthentifie()->request('POST', '/api/projets/019f0000-0000-7000-8000-000000000000/scans', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjetAvecPage(object $client): string
    {
        $reponse = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet scan', 'client' => 'Client', 'type' => 'rgaa', 'cible' => 'https://exemple.fr'],
        ]);
        /** @var array{id: string} $data */
        $data = $reponse->toArray();
        $client->request('POST', '/api/projets/'.$data['id'].'/pages', [
            'json' => ['url' => 'https://exemple.fr/contact', 'titre' => 'Contact'],
        ]);

        return $data['id'];
    }
}
