<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210330025605 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE individual_name (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', individual_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(120) NOT NULL, UNIQUE INDEX UNIQ_96E1E4B5AE271C0D (individual_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE individual_name ADD CONSTRAINT FK_96E1E4B5AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) NOT NULL, CHANGE form form VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE individual ADD name_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE individual ADD CONSTRAINT FK_8793FC1771179CD6 FOREIGN KEY (name_id) REFERENCES individual_name (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8793FC1771179CD6 ON individual (name_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE individual DROP FOREIGN KEY FK_8793FC1771179CD6');
        $this->addSql('DROP TABLE individual_name');
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE form form VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX UNIQ_8793FC1771179CD6 ON individual');
        $this->addSql('ALTER TABLE individual DROP name_id');
    }
}
