<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210401030226 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribute (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source VARCHAR(255) NOT NULL, email VARCHAR(120) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', place CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', event_source VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3BAE0AA7741D53CD (place), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', identifier SMALLINT NOT NULL, UNIQUE INDEX identifier (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family_events (family_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_9AB7005FC35E566A (family_id), INDEX IDX_9AB7005F71F7E88B (event_id), PRIMARY KEY(family_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gedcom (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', version VARCHAR(32) NOT NULL, form VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', gedcom CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', `char` VARCHAR(32) NOT NULL, lang VARCHAR(32) NOT NULL, source VARCHAR(191) NOT NULL, destination VARCHAR(191) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', file VARCHAR(191) NOT NULL, UNIQUE INDEX gedcom (gedcom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', identifier SMALLINT NOT NULL, gender VARCHAR(255) DEFAULT \'N\' NOT NULL, UNIQUE INDEX UNIQ_8793FC175E237E06 (name), INDEX name (name), UNIQUE INDEX identifier (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_events (individual_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8CB518A9AE271C0D (individual_id), INDEX IDX_8CB518A971F7E88B (event_id), PRIMARY KEY(individual_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_source (individual_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_80B83090AE271C0D (individual_id), INDEX IDX_80B83090953C1C61 (source_id), PRIMARY KEY(individual_id, source_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_family (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', individual CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', family CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', relationship_type VARCHAR(255) NOT NULL, INDEX IDX_7AD46EB88793FC17 (individual), INDEX IDX_7AD46EB8A5E6215B (family), UNIQUE INDEX individual_family (individual, family), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_name (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', individual_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(120) NOT NULL, name_type VARCHAR(255) DEFAULT NULL, given_name VARCHAR(120) DEFAULT NULL, surname VARCHAR(120) DEFAULT NULL, nick_name VARCHAR(30) DEFAULT NULL, name_prefix VARCHAR(30) DEFAULT NULL, surname_prefix VARCHAR(30) DEFAULT NULL, name_suffix VARCHAR(30) DEFAULT NULL, note LONGTEXT DEFAULT NULL, source LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_96E1E4B5AE271C0D (individual_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE place (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(120) NOT NULL, source VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE source (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', identifier SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7741D53CD FOREIGN KEY (place) REFERENCES place (id)');
        $this->addSql('ALTER TABLE family_events ADD CONSTRAINT FK_9AB7005FC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE family_events ADD CONSTRAINT FK_9AB7005F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE header ADD CONSTRAINT FK_6E72A8C1DA5219DC FOREIGN KEY (gedcom) REFERENCES gedcom (id)');
        $this->addSql('ALTER TABLE individual ADD CONSTRAINT FK_8793FC175E237E06 FOREIGN KEY (name) REFERENCES individual_name (id)');
        $this->addSql('ALTER TABLE individual_events ADD CONSTRAINT FK_8CB518A9AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_events ADD CONSTRAINT FK_8CB518A971F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE individual_source ADD CONSTRAINT FK_80B83090AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_source ADD CONSTRAINT FK_80B83090953C1C61 FOREIGN KEY (source_id) REFERENCES source (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_family ADD CONSTRAINT FK_7AD46EB88793FC17 FOREIGN KEY (individual) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_family ADD CONSTRAINT FK_7AD46EB8A5E6215B FOREIGN KEY (family) REFERENCES family (id)');
        $this->addSql('ALTER TABLE individual_name ADD CONSTRAINT FK_96E1E4B5AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE family_events DROP FOREIGN KEY FK_9AB7005F71F7E88B');
        $this->addSql('ALTER TABLE individual_events DROP FOREIGN KEY FK_8CB518A971F7E88B');
        $this->addSql('ALTER TABLE family_events DROP FOREIGN KEY FK_9AB7005FC35E566A');
        $this->addSql('ALTER TABLE individual_family DROP FOREIGN KEY FK_7AD46EB8A5E6215B');
        $this->addSql('ALTER TABLE header DROP FOREIGN KEY FK_6E72A8C1DA5219DC');
        $this->addSql('ALTER TABLE individual_events DROP FOREIGN KEY FK_8CB518A9AE271C0D');
        $this->addSql('ALTER TABLE individual_source DROP FOREIGN KEY FK_80B83090AE271C0D');
        $this->addSql('ALTER TABLE individual_family DROP FOREIGN KEY FK_7AD46EB88793FC17');
        $this->addSql('ALTER TABLE individual_name DROP FOREIGN KEY FK_96E1E4B5AE271C0D');
        $this->addSql('ALTER TABLE individual DROP FOREIGN KEY FK_8793FC175E237E06');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7741D53CD');
        $this->addSql('ALTER TABLE individual_source DROP FOREIGN KEY FK_80B83090953C1C61');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE family_events');
        $this->addSql('DROP TABLE gedcom');
        $this->addSql('DROP TABLE header');
        $this->addSql('DROP TABLE individual');
        $this->addSql('DROP TABLE individual_events');
        $this->addSql('DROP TABLE individual_source');
        $this->addSql('DROP TABLE individual_family');
        $this->addSql('DROP TABLE individual_name');
        $this->addSql('DROP TABLE place');
        $this->addSql('DROP TABLE source');
    }
}
