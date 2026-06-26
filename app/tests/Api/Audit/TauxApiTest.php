<?php

declare(strict_types=1);

namespace App\Tests\Api\Audit;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Spécification d'acceptation du Lot A4 : surcharge manuelle d'un constat et
 * impact sur le taux de conformité.
 */
final class TauxApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const URL_PAGE = 'https://exemple.fr/accueil';

    protected function setUp(): void
    {
        parent::setUp();
        $this->purger();
    }

    public function testLaSurchargeManuellePrimeEtModifieLeTaux(): void
    {
        $client = self::createClient();
        $projetId = $this->creerProjetScanne($client);

        $avant = $client->request('GET', '/api/projets/'.$projetId.'/taux')->toArray();
        self::assertGreaterThan(0, $avant['nonConformes']);

        // Le critère 1.1 (image-alt → wcag111) est non conforme en auto ; on le passe conforme.
        $client->request('PUT', '/api/projets/'.$projetId.'/constats', [
            'json' => ['pageUrl' => self::URL_PAGE, 'critereNumero' => '1.1', 'statut' => 'conforme', 'commentaire' => 'Vérifié manuellement.'],
        ]);
        $this->assertResponseStatusCodeSame(204);

        $apres = $client->request('GET', '/api/projets/'.$projetId.'/taux')->toArray();
        self::assertSame($avant['conformes'] + 1, $apres['conformes']);
        self::assertSame($avant['nonConformes'] - 1, $apres['nonConformes']);

        $constats = $client->request('GET', '/api/projets/'.$projetId.'/constats')->toArray();
        $constat11 = $this->parCritere($constats, '1.1');
        self::assertSame('conforme', $constat11['statut']);
        self::assertSame('manuel', $constat11['source']);
    }

    public function testSurchargeSurProjetInconnuRenvoie404(): void
    {
        self::createClient()->request('PUT', '/api/projets/019f0000-0000-7000-8000-000000000000/constats', [
            'json' => ['pageUrl' => self::URL_PAGE, 'critereNumero' => '1.1', 'statut' => 'conforme'],
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    private function creerProjetScanne(object $client): string
    {
        $data = $client->request('POST', '/api/projets', [
            'json' => ['nom' => 'Projet taux', 'client' => 'Client', 'urlReference' => 'https://exemple.fr'],
        ])->toArray();
        $projetId = is_string($data['id']) ? $data['id'] : '';
        $client->request('POST', '/api/projets/'.$projetId.'/pages', ['json' => ['url' => self::URL_PAGE, 'titre' => 'Accueil']]);
        $client->request('POST', '/api/projets/'.$projetId.'/scans', ['json' => (object) []]);

        return $projetId;
    }

    /**
     * @param array<int, array{critereNumero: string, statut: string, source: string}> $constats
     *
     * @return array{critereNumero: string, statut: string, source: string}
     */
    private function parCritere(array $constats, string $critere): array
    {
        foreach ($constats as $constat) {
            if ($critere === $constat['critereNumero']) {
                return $constat;
            }
        }

        self::fail(sprintf('Aucun constat pour le critère %s.', $critere));
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
