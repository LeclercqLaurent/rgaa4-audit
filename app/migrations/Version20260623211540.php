<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623211540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rgaa_critere (wcag JSON NOT NULL, techniques JSON NOT NULL, numero VARCHAR(8) NOT NULL, intitule LONGTEXT NOT NULL, thematique_numero INT NOT NULL, INDEX IDX_D1761E51AB9E00BE (thematique_numero), PRIMARY KEY (numero)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rgaa_test (enonces JSON NOT NULL, numero VARCHAR(12) NOT NULL, critere_numero VARCHAR(8) NOT NULL, INDEX IDX_4F6334FBE03A3EC9 (critere_numero), PRIMARY KEY (numero)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rgaa_thematique (numero INT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY (numero)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE rgaa_critere ADD CONSTRAINT FK_D1761E51AB9E00BE FOREIGN KEY (thematique_numero) REFERENCES rgaa_thematique (numero)');
        $this->addSql('ALTER TABLE rgaa_test ADD CONSTRAINT FK_4F6334FBE03A3EC9 FOREIGN KEY (critere_numero) REFERENCES rgaa_critere (numero)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rgaa_critere DROP FOREIGN KEY FK_D1761E51AB9E00BE');
        $this->addSql('ALTER TABLE rgaa_test DROP FOREIGN KEY FK_4F6334FBE03A3EC9');
        $this->addSql('DROP TABLE rgaa_critere');
        $this->addSql('DROP TABLE rgaa_test');
        $this->addSql('DROP TABLE rgaa_thematique');
    }
}
