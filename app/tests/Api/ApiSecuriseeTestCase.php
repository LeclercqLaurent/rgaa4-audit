<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Domain\Identity\Entity\Utilisateur;
use App\Domain\Identity\Port\UtilisateurRepository;
use App\Domain\Identity\ValueObject\Email;
use App\Domain\Identity\ValueObject\RoleUtilisateur;
use App\Domain\Shared\Port\IdGenerator;
use App\Infrastructure\Identity\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base des tests d'API sur les espaces sécurisés : fournit un client porteur
 * d'un JWT valide (jeton forgé via le JWT manager, sans aller-retour HTTP).
 */
abstract class ApiSecuriseeTestCase extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected const EMAIL_AUDITEUR = 'auditeur@rgaa.test';

    protected function clientAuthentifie(): Client
    {
        $client = self::createClient();
        $this->garantirAuditeur();

        $jeton = self::getContainer()->get(JWTTokenManagerInterface::class)
            ->create(new SecurityUser(self::EMAIL_AUDITEUR, '', [RoleUtilisateur::Auditeur->value]));

        $client->setDefaultOptions(['headers' => ['Authorization' => 'Bearer '.$jeton]]);

        return $client;
    }

    /**
     * Cible d'un projet « Complexité PHP » : le domaine du dépôt lui-même.
     *
     * Le code analysé doit exister partout où la suite tourne. Viser
     * l'installation locale de l'outil d'analyse faisait dépendre les tests
     * d'un montage propre à un poste, ce qui ne se voyait qu'ailleurs.
     *
     * Le domaine est choisi pour ses 78 méthodes : un dossier de Value Objects
     * et d'interfaces n'en expose aucune, et l'analyse ne produirait rien à
     * vérifier.
     */
    protected function cibleComplexite(): string
    {
        $racine = self::getContainer()->getParameter('kernel.project_dir');

        return (is_string($racine) ? $racine : '').'/src/Domain';
    }

    protected function garantirAuditeur(): void
    {
        $utilisateurs = self::getContainer()->get(UtilisateurRepository::class);

        if (null !== $utilisateurs->parEmail(self::EMAIL_AUDITEUR)) {
            return;
        }

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $hache = $hasher->hashPassword(new SecurityUser(self::EMAIL_AUDITEUR, '', []), 'motdepasse-tres-long-2026');
        $ids = self::getContainer()->get(IdGenerator::class);

        $utilisateurs->save(new Utilisateur($ids->generate(), new Email(self::EMAIL_AUDITEUR), $hache, [RoleUtilisateur::Auditeur]));
    }

    protected function purgerAudit(): void
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['audit_constat', 'scan', 'audit_page', 'audit_projet'] as $table) {
            $connection->executeStatement('TRUNCATE TABLE '.$table);
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
