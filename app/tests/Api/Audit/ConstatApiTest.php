<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use App\Tests\Api\ApiSecuriseeTestCase;

/**
 * Spécification d'acceptation du Lot A3 : un scan terminé alimente les constats
 * de conformité RGAA (mapping axe → WCAG → RGAA), avec preuves.
 */
final class ConstatApiTest extends ApiSecuriseeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purgerAudit();
    }

    public function testUnScanAlimenteLesConstatsRgaa(): void
    {
        $client = $this->clientAuthentifie();
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
}
