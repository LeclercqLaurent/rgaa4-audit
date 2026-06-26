<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Contexte Audit : tables des projets d'audit et de leur échantillon de pages.
 */
final class Version20260626122958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contexte Audit : audit_projet + audit_page (codes en ascii_bin, texte en utf8mb4_uca1400_ai_ci).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_page (id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, url VARCHAR(2048) NOT NULL, titre VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, projet_id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, INDEX IDX_32AA5824C18272 (projet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE audit_projet (id VARCHAR(36) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, nom VARCHAR(150) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, client VARCHAR(150) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, url_reference VARCHAR(2048) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE audit_page ADD CONSTRAINT FK_32AA5824C18272 FOREIGN KEY (projet_id) REFERENCES audit_projet (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_page DROP FOREIGN KEY FK_32AA5824C18272');
        $this->addSql('DROP TABLE audit_page');
        $this->addSql('DROP TABLE audit_projet');
    }
}
