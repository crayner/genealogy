<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210330230753 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) NOT NULL, CHANGE form form VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE individual_name ADD name_type VARCHAR(255) DEFAULT NULL, ADD given_name VARCHAR(120) DEFAULT NULL, ADD surname VARCHAR(120) DEFAULT NULL, ADD nick_name VARCHAR(30) DEFAULT NULL, ADD name_prefix VARCHAR(30) DEFAULT NULL, ADD surname_prefix VARCHAR(30) DEFAULT NULL, ADD name_suffix VARCHAR(30) DEFAULT NULL, ADD note LONGTEXT DEFAULT NULL, ADD source LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE form form VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE individual_name DROP name_type, DROP given_name, DROP surname, DROP nick_name, DROP name_prefix, DROP surname_prefix, DROP name_suffix, DROP note, DROP source');
    }
}
