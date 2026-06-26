<?php

declare(strict_types=1);

namespace App\Tests\Api\Identity;

use App\Tests\Api\ApiSecuriseeTestCase;

/**
 * Spécification d'acceptation du Lot A7 : authentification JWT et cloisonnement
 * des espaces.
 */
final class IdentityApiTest extends ApiSecuriseeTestCase
{
    public function testLoginRetourneUnJeton(): void
    {
        $client = self::createClient();
        $this->garantirAuditeur();

        $client->request('POST', '/api/login_check', [
            'json' => ['email' => self::EMAIL_AUDITEUR, 'motDePasse' => 'motdepasse-tres-long-2026'],
        ]);

        $this->assertResponseIsSuccessful();
        self::assertArrayHasKey('token', $client->getResponse()->toArray());
    }

    public function testMauvaisMotDePasseEstRejete(): void
    {
        $client = self::createClient();
        $this->garantirAuditeur();

        $client->request('POST', '/api/login_check', [
            'json' => ['email' => self::EMAIL_AUDITEUR, 'motDePasse' => 'mauvais'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLeReferentielRestePublic(): void
    {
        self::createClient()->request('GET', '/api/thematiques');

        $this->assertResponseIsSuccessful();
    }
}
