<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use App\Tests\Api\ApiSecuriseeTestCase;

/**
 * Point d'entrée unifié de déclenchement d'audit (POST /api/projets/{id}/auditer) :
 * le moteur du référentiel décide du mode (asynchrone RGAA / synchrone complexité).
 */
final class AuditApiTest extends ApiSecuriseeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purgerAudit();
    }

    public function testProjetRgaaDeclencheUnAuditAsynchrone(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client, 'rgaa', 'https://exemple.fr');

        $reponse = $client->request('POST', '/api/projets/'.$id.'/auditer', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(202);
        self::assertSame(['synchrone' => false, 'constatsGeneres' => null], $reponse->toArray());
    }

    public function testProjetComplexiteDeclencheUnAuditSynchrone(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client, 'complexite_php', '/opt/phpx/src');

        $reponse = $client->request('POST', '/api/projets/'.$id.'/auditer', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(200);
        /** @var array{synchrone: bool, constatsGeneres: int} $data */
        $data = $reponse->toArray();
        self::assertTrue($data['synchrone']);
        self::assertGreaterThan(0, $data['constatsGeneres']);
    }

    public function testAuditProjetInconnuRenvoie404(): void
    {
        $this->clientAuthentifie()->request('POST', '/api/projets/019f0000-0000-7000-8000-000000000000/auditer', ['json' => (object) []]);

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjet(object $client, string $type, string $cible): string
    {
        /** @var array{id: string} $data */
        $data = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet audit', 'client' => 'Client', 'type' => $type, 'cible' => $cible],
        ])->toArray();

        return $data['id'];
    }
}
