<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Contexte Scan : table des scans automatiques (résultats axe-core par page en JSON).
 */
final class Version20260626130607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contexte Scan : table scan (statut, résultats axe-core par page en JSON).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE scan (id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, projet_id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, statut VARCHAR(12) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, date_creation DATETIME NOT NULL, resultats JSON NOT NULL, date_fin DATETIME DEFAULT NULL, erreur LONGTEXT DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE scan');
    }
}
