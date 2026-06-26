<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use App\Infrastructure\Persistence\Entity\ScanEntity;
use App\Tests\Api\ApiSecuriseeTestCase;
use Doctrine\ORM\EntityManagerInterface;

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
                'type' => 'rgaa',
                'cible' => 'https://exemple.fr',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'nom' => 'Audit site mairie',
            'client' => 'Mairie de Démo',
            'type' => 'rgaa',
            'cible' => 'https://exemple.fr',
            'pages' => [],
        ]);
    }

    public function testCreerUnProjetDeComplexiteAccepteUnCheminCode(): void
    {
        $client = $this->clientAuthentifie();

        $client->request('POST', '/api/projets', [
            'json' => [
                'nom' => 'Audit complexité',
                'client' => 'Interne',
                'type' => 'complexite_php',
                'cible' => '/var/www/app/src',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'type' => 'complexite_php',
            'cible' => '/var/www/app/src',
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
            'json' => ['nom' => 'X', 'client' => 'Y', 'type' => 'rgaa', 'cible' => 'pas-une-url'],
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

    public function testModifierUnProjet(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client);

        $client->request('PATCH', '/api/projets/'.$id, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['nom' => 'Audit renommé', 'client' => 'Nouveau client', 'cible' => 'https://exemple.fr']),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['id' => $id, 'nom' => 'Audit renommé', 'client' => 'Nouveau client']);
    }

    public function testSupprimerUnProjetEtSesDonnees(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client);
        $client->request('POST', '/api/projets/'.$id.'/pages', ['json' => ['url' => 'https://exemple.fr/a', 'titre' => 'A']]);
        $client->request('POST', '/api/projets/'.$id.'/scans', ['json' => (object) []]);

        $client->request('DELETE', '/api/projets/'.$id);
        $this->assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/projets/'.$id);
        $this->assertResponseStatusCodeSame(404);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $scansRestants = $em->getRepository(ScanEntity::class)->count(['projetId' => $id]);
        self::assertSame(0, $scansRestants, 'Les scans du projet doivent être supprimés en cascade.');
    }

    public function testSupprimerUnProjetInconnuRenvoie404(): void
    {
        $this->clientAuthentifie()->request('DELETE', '/api/projets/019f0000-0000-7000-8000-000000000000');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testModifierUnePageDeLEchantillon(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client);
        $page = $client->request('POST', '/api/projets/'.$id.'/pages', ['json' => ['url' => 'https://exemple.fr/a', 'titre' => 'A']]);
        /** @var array{pages: list<array{id: string}>} $projet */
        $projet = $page->toArray();
        $pageId = $projet['pages'][0]['id'];

        $client->request('PATCH', '/api/projets/'.$id.'/pages/'.$pageId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['url' => 'https://exemple.fr/accueil', 'titre' => 'Accueil']),
        ]);
        $this->assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/projets/'.$id);
        $this->assertJsonContains(['pages' => [['url' => 'https://exemple.fr/accueil', 'titre' => 'Accueil']]]);
    }

    public function testSupprimerUnePageDeLEchantillon(): void
    {
        $client = $this->clientAuthentifie();
        $id = $this->creerProjet($client);
        $page = $client->request('POST', '/api/projets/'.$id.'/pages', ['json' => ['url' => 'https://exemple.fr/a', 'titre' => 'A']]);
        /** @var array{pages: list<array{id: string}>} $projet */
        $projet = $page->toArray();
        $pageId = $projet['pages'][0]['id'];

        $client->request('DELETE', '/api/projets/'.$id.'/pages/'.$pageId);
        $this->assertResponseStatusCodeSame(204);

        $reponse = $client->request('GET', '/api/projets/'.$id);
        /** @var array{pages: list<mixed>} $apres */
        $apres = $reponse->toArray();
        self::assertCount(0, $apres['pages']);
    }

    private function creerProjet(object $client): string
    {
        $reponse = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet test', 'client' => 'Client test', 'type' => 'rgaa', 'cible' => 'https://exemple.fr'],
        ]);

        /** @var array{id: string} $data */
        $data = $reponse->toArray();

        return $data['id'];
    }
}
