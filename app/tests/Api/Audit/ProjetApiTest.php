<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use App\Tests\Api\ApiSecuriseeTestCase;

/**
 * Spécification d'acceptation du contexte Audit (Lot A1) : création de projet
 * et constitution de l'échantillon de pages, sur l'espace sécurisé (JWT).
 *
 * Note d'écart au socle : Behat (Gherkin) n'est pas compatible Symfony 8.1
 * (contrainte symfony/console ≤ 7.x). On exprime donc les scénarios
 * d'acceptation via ApiTestCase (PHPUnit), même intention « specs exécutables ».
 */
final class ProjetApiTest extends ApiSecuriseeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purgerAudit();
    }

    public function testCreerUnProjetDAudit(): void
    {
        $client = $this->clientAuthentifie();

        $client->request('POST', '/api/projets', [
            'json' => [
                'nom' => 'Audit site mairie',
                'client' => 'Mairie de Démo',
                'urlReference' => 'https://exemple.fr',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'nom' => 'Audit site mairie',
            'client' => 'Mairie de Démo',
            'urlReference' => 'https://exemple.fr',
            'pages' => [],
        ]);
    }

    public function testAjouterUnePageALEchantillon(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client);

        $client->request('POST', '/api/projets/'.$id.'/pages', [
            'json' => ['url' => 'https://exemple.fr/contact', 'titre' => 'Contact'],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'id' => $id,
            'pages' => [['url' => 'https://exemple.fr/contact', 'titre' => 'Contact']],
        ]);
    }

    public function testUrlDeReferenceInvalideEstRejetee(): void
    {
        $client = $this->clientAuthentifie();

        $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'X', 'client' => 'Y', 'urlReference' => 'pas-une-url'],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testAccesRefuseSansJeton(): void
    {
        self::createClient()->request('GET', '/api/projets');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAjouterUnePageSurUnProjetInconnuRenvoie404(): void
    {
        $this->clientAuthentifie()->request('POST', '/api/projets/019f0000-0000-7000-8000-000000000000/pages', [
            'json' => ['url' => 'https://exemple.fr/x', 'titre' => 'X'],
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjet(object $client): string
    {
        $reponse = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet test', 'client' => 'Client test', 'urlReference' => 'https://exemple.fr'],
        ]);

        /** @var array{id: string} $data */
        $data = $reponse->toArray();

        return $data['id'];
    }
}
