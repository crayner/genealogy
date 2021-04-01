<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210401221230 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute CHANGE source source VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE event_source event_source VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE age age VARCHAR(13) DEFAULT NULL');
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) NOT NULL, CHANGE form form VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE individual CHANGE gender gender VARCHAR(255) DEFAULT \'N\' NOT NULL');
        $this->addSql('ALTER TABLE individual_family CHANGE relationship_type relationship_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE individual_name CHANGE name_type name_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE place CHANGE source source VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE source_data CHANGE quality_of_data quality_of_data VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute CHANGE source source VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE event CHANGE event_source event_source VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE age age VARCHAR(13) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE gedcom CHANGE version version VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE form form VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE individual CHANGE gender gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'N\' NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE individual_family CHANGE relationship_type relationship_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE individual_name CHANGE name_type name_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE place CHANGE source source VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE source_data CHANGE quality_of_data quality_of_data VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
