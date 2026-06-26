<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Lot A3 : tables audit_constat (constats de conformité) et rgaa_mapping_axe (mapping axe → RGAA).
 */
final class Version20260626133337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Lot A3 : audit_constat + rgaa_mapping_axe.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_constat (id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, projet_id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, page_url VARCHAR(2048) NOT NULL, critere_numero VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, statut VARCHAR(16) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, source VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, preuves JSON NOT NULL, commentaire LONGTEXT DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, INDEX idx_constat_projet (projet_id), UNIQUE INDEX uniq_constat (projet_id, page_url, critere_numero, source), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rgaa_mapping_axe (axe_tag VARCHAR(32) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, critere_numero VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, PRIMARY KEY (axe_tag, critere_numero)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_constat');
        $this->addSql('DROP TABLE rgaa_mapping_axe');
    }
}
