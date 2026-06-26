<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Spécification d'acceptation du contexte Audit (Lot A1) : création de projet
 * et constitution de l'échantillon de pages, exprimée au niveau de l'API REST.
 *
 * Note d'écart au socle : Behat (Gherkin) n'est pas compatible Symfony 8.1
 * (contrainte symfony/console ≤ 7.x). On exprime donc les scénarios
 * d'acceptation via ApiTestCase (PHPUnit), même intention « specs exécutables ».
 */
final class ProjetApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purgerAudit();
    }

    public function testCreerUnProjetDAudit(): void
    {
        $client = self::createClient();

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
        $client = self::createClient();
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
        $client = self::createClient();

        $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'X', 'client' => 'Y', 'urlReference' => 'pas-une-url'],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testAjouterUnePageSurUnProjetInconnuRenvoie404(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/projets/019f0000-0000-7000-8000-000000000000/pages', [
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

    private function purgerAudit(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE audit_page');
        $connection->executeStatement('TRUNCATE TABLE audit_projet');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
