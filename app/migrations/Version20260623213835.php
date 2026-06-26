<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623213835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Référentiel RGAA : codes (PK/FK) en ascii_bin, texte (nom/intitulé) en utf8mb4_uca1400_ai_ci.';
    }

    public function up(Schema $schema): void
    {
        // La FK rgaa_test -> rgaa_critere doit être supprimée avant de changer la
        // collation de la PK rgaa_critere.numero (impossible tant qu'elle est référencée).
        $this->addSql('ALTER TABLE rgaa_test DROP FOREIGN KEY FK_4F6334FBE03A3EC9');

        // Colonnes « code » (PK/FK) -> ascii_bin.
        $this->addSql('ALTER TABLE rgaa_critere CHANGE numero numero VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`');
        $this->addSql('ALTER TABLE rgaa_test CHANGE numero numero VARCHAR(12) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, CHANGE critere_numero critere_numero VARCHAR(8) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`');

        // Recréation de la FK (les deux colonnes partagent désormais ascii_bin).
        $this->addSql('ALTER TABLE rgaa_test ADD CONSTRAINT FK_4F6334FBE03A3EC9 FOREIGN KEY (critere_numero) REFERENCES rgaa_critere (numero)');

        // Colonnes texte FR -> collation Unicode moderne. Ajouté à la main : DBAL ne
        // détecte pas un changement de collation à charset égal (utf8mb4 -> utf8mb4).
        $this->addSql('ALTER TABLE rgaa_thematique CHANGE nom nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE rgaa_critere CHANGE intitule intitule LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rgaa_test DROP FOREIGN KEY FK_4F6334FBE03A3EC9');
        $this->addSql('ALTER TABLE rgaa_critere CHANGE numero numero VARCHAR(8) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE rgaa_test CHANGE numero numero VARCHAR(12) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE critere_numero critere_numero VARCHAR(8) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE rgaa_test ADD CONSTRAINT FK_4F6334FBE03A3EC9 FOREIGN KEY (critere_numero) REFERENCES rgaa_critere (numero)');
        $this->addSql('ALTER TABLE rgaa_thematique CHANGE nom nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE rgaa_critere CHANGE intitule intitule LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
    }
}
