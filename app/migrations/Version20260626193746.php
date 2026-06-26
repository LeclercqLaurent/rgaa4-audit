<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Projets typés : ajoute audit_projet.type (RGAA / Complexité). Les projets
 * existants sont rattachés au type RGAA.
 */
final class Version20260626193746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Projets typés : audit_projet.type (existants → rgaa).';
    }

    public function up(Schema $schema): void
    {
        // Ajout avec défaut pour peupler l'existant, puis retrait du défaut
        // (le type est fourni explicitement par l'entité).
        $this->addSql('ALTER TABLE audit_projet ADD type VARCHAR(20) CHARACTER SET ascii NOT NULL DEFAULT \'rgaa\' COLLATE `ascii_bin`');
        $this->addSql('ALTER TABLE audit_projet ALTER COLUMN type DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_projet DROP type');
    }
}
