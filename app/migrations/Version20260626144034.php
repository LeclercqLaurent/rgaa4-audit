<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Lot A7 : table utilisateur (auth JWT, rôles, mot de passe Argon2id).
 */
final class Version20260626144034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Lot A7 : table utilisateur.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE utilisateur (id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX uniq_utilisateur_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE utilisateur');
    }
}
