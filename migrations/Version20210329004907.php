<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210329004907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gedcom (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', version VARCHAR(32) NOT NULL, form VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', gedcom CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', `char` VARCHAR(32) NOT NULL, lang VARCHAR(32) NOT NULL, source VARCHAR(191) NOT NULL, UNIQUE INDEX gedcom (gedcom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE header ADD CONSTRAINT FK_6E72A8C1DA5219DC FOREIGN KEY (gedcom) REFERENCES gedcom (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE header DROP FOREIGN KEY FK_6E72A8C1DA5219DC');
        $this->addSql('DROP TABLE gedcom');
        $this->addSql('DROP TABLE header');
    }
}
