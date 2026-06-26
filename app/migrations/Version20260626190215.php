<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase C1 : audit_constat porte le référentiel + critere_numero élargi (VARCHAR 20).
 */
final class Version20260626190215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase C1 : audit_constat.referentiel + critere_numero VARCHAR(20).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_constat ON audit_constat');
        $this->addSql('ALTER TABLE audit_constat ADD referentiel VARCHAR(20) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, CHANGE critere_numero critere_numero VARCHAR(20) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`');
        $this->addSql('CREATE UNIQUE INDEX uniq_constat ON audit_constat (projet_id, referentiel, page_url, critere_numero, source)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_constat ON audit_constat');
        $this->addSql('ALTER TABLE audit_constat DROP referentiel, CHANGE critere_numero critere_numero VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`');
        $this->addSql('CREATE UNIQUE INDEX uniq_constat ON audit_constat (projet_id, page_url, critere_numero, source)');
    }
}
