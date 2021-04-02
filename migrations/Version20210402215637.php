<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210402215637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribute (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', email VARCHAR(120) DEFAULT NULL, type VARCHAR(255) NOT NULL, place VARCHAR(120) NOT NULL, INDEX source (source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source_data_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', content LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_ADF3F3637E2A1954 (source_data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', place CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', source CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', name VARCHAR(90) DEFAULT NULL, event_source VARCHAR(191) NOT NULL, type VARCHAR(191) NOT NULL, age VARCHAR(13) DEFAULT NULL, cause VARCHAR(90) DEFAULT NULL, note LONGTEXT DEFAULT NULL, role VARCHAR(27) NOT NULL, INDEX source (source), UNIQUE INDEX place (place), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', identifier SMALLINT NOT NULL, UNIQUE INDEX identifier (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family_events (family_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_9AB7005FC35E566A (family_id), INDEX IDX_9AB7005F71F7E88B (event_id), PRIMARY KEY(family_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gedcom (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', version VARCHAR(32) NOT NULL, form VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', gedcom CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', `char` VARCHAR(32) NOT NULL, lang VARCHAR(32) NOT NULL, source VARCHAR(191) NOT NULL, destination VARCHAR(191) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', file VARCHAR(191) NOT NULL, UNIQUE INDEX gedcom (gedcom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', identifier SMALLINT NOT NULL, gender VARCHAR(255) DEFAULT \'N\' NOT NULL, record_key BIGINT NOT NULL, note LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX identifier (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_events (individual_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', event_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8CB518A9AE271C0D (individual_id), INDEX IDX_8CB518A971F7E88B (event_id), PRIMARY KEY(individual_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_source_data (individual_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source_data_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_21356B2AAE271C0D (individual_id), INDEX IDX_21356B2A7E2A1954 (source_data_id), PRIMARY KEY(individual_id, source_data_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_multimedia_records (individual_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', multimedia_record_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_FC9403B1AE271C0D (individual_id), INDEX IDX_FC9403B1673E5F6B (multimedia_record_id), PRIMARY KEY(individual_id, multimedia_record_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_family (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', individual CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', family CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', relationship_type VARCHAR(255) NOT NULL, INDEX IDX_7AD46EB88793FC17 (individual), INDEX IDX_7AD46EB8A5E6215B (family), UNIQUE INDEX individual_family (individual, family), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_name (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', individual CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', source CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(120) NOT NULL, name_type VARCHAR(255) DEFAULT NULL, given_name VARCHAR(120) DEFAULT NULL, surname VARCHAR(120) DEFAULT NULL, nick_name VARCHAR(30) DEFAULT NULL, name_prefix VARCHAR(30) DEFAULT NULL, surname_prefix VARCHAR(30) DEFAULT NULL, name_suffix VARCHAR(30) DEFAULT NULL, note LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_96E1E4B55F8A7F73 (source), INDEX individual (individual), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multimedia_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', reference LONGTEXT NOT NULL, format VARCHAR(4) NOT NULL, media_type VARCHAR(15) DEFAULT NULL, title LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multimedia_record (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', link VARCHAR(36) NOT NULL, record_key VARCHAR(12) DEFAULT NULL, note LONGTEXT DEFAULT NULL, change_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX UNIQ_2C7DA8615F8A7F73 (source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multimedia_record_multimedia_file (multimedia_record_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', multimedia_file_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_A489D14E673E5F6B (multimedia_record_id), INDEX IDX_A489D14E2EC1C247 (multimedia_file_id), PRIMARY KEY(multimedia_record_id, multimedia_file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE place (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(120) NOT NULL, source VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE source (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', identifier VARCHAR(22) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE source_data (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', data_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', page LONGTEXT DEFAULT NULL, quality_of_data VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_AC7976605F8A7F73 (source), UNIQUE INDEX UNIQ_AC79766037F5A13C (data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reference (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', reference VARCHAR(20) NOT NULL, type VARCHAR(40) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribute ADD CONSTRAINT FK_FA7AEFFB5F8A7F73 FOREIGN KEY (source) REFERENCES source_data (id)');
        $this->addSql('ALTER TABLE data ADD CONSTRAINT FK_ADF3F3637E2A1954 FOREIGN KEY (source_data_id) REFERENCES source_data (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7741D53CD FOREIGN KEY (place) REFERENCES place (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA75F8A7F73 FOREIGN KEY (source) REFERENCES source_data (id)');
        $this->addSql('ALTER TABLE family_events ADD CONSTRAINT FK_9AB7005FC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE family_events ADD CONSTRAINT FK_9AB7005F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE header ADD CONSTRAINT FK_6E72A8C1DA5219DC FOREIGN KEY (gedcom) REFERENCES gedcom (id)');
        $this->addSql('ALTER TABLE individual_events ADD CONSTRAINT FK_8CB518A9AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_events ADD CONSTRAINT FK_8CB518A971F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE individual_source_data ADD CONSTRAINT FK_21356B2AAE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_source_data ADD CONSTRAINT FK_21356B2A7E2A1954 FOREIGN KEY (source_data_id) REFERENCES source_data (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_multimedia_records ADD CONSTRAINT FK_FC9403B1AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_multimedia_records ADD CONSTRAINT FK_FC9403B1673E5F6B FOREIGN KEY (multimedia_record_id) REFERENCES multimedia_record (id)');
        $this->addSql('ALTER TABLE individual_family ADD CONSTRAINT FK_7AD46EB88793FC17 FOREIGN KEY (individual) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_family ADD CONSTRAINT FK_7AD46EB8A5E6215B FOREIGN KEY (family) REFERENCES family (id)');
        $this->addSql('ALTER TABLE individual_name ADD CONSTRAINT FK_96E1E4B58793FC17 FOREIGN KEY (individual) REFERENCES individual (id)');
        $this->addSql('ALTER TABLE individual_name ADD CONSTRAINT FK_96E1E4B55F8A7F73 FOREIGN KEY (source) REFERENCES source_data (id)');
        $this->addSql('ALTER TABLE multimedia_record ADD CONSTRAINT FK_2C7DA8615F8A7F73 FOREIGN KEY (source) REFERENCES source_data (id)');
        $this->addSql('ALTER TABLE multimedia_record_multimedia_file ADD CONSTRAINT FK_A489D14E673E5F6B FOREIGN KEY (multimedia_record_id) REFERENCES multimedia_record (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE multimedia_record_multimedia_file ADD CONSTRAINT FK_A489D14E2EC1C247 FOREIGN KEY (multimedia_file_id) REFERENCES multimedia_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE source_data ADD CONSTRAINT FK_AC7976605F8A7F73 FOREIGN KEY (source) REFERENCES source (id)');
        $this->addSql('ALTER TABLE source_data ADD CONSTRAINT FK_AC79766037F5A13C FOREIGN KEY (data_id) REFERENCES data (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE source_data DROP FOREIGN KEY FK_AC79766037F5A13C');
        $this->addSql('ALTER TABLE family_events DROP FOREIGN KEY FK_9AB7005F71F7E88B');
        $this->addSql('ALTER TABLE individual_events DROP FOREIGN KEY FK_8CB518A971F7E88B');
        $this->addSql('ALTER TABLE family_events DROP FOREIGN KEY FK_9AB7005FC35E566A');
        $this->addSql('ALTER TABLE individual_family DROP FOREIGN KEY FK_7AD46EB8A5E6215B');
        $this->addSql('ALTER TABLE header DROP FOREIGN KEY FK_6E72A8C1DA5219DC');
        $this->addSql('ALTER TABLE individual_events DROP FOREIGN KEY FK_8CB518A9AE271C0D');
        $this->addSql('ALTER TABLE individual_source_data DROP FOREIGN KEY FK_21356B2AAE271C0D');
        $this->addSql('ALTER TABLE individual_multimedia_records DROP FOREIGN KEY FK_FC9403B1AE271C0D');
        $this->addSql('ALTER TABLE individual_family DROP FOREIGN KEY FK_7AD46EB88793FC17');
        $this->addSql('ALTER TABLE individual_name DROP FOREIGN KEY FK_96E1E4B58793FC17');
        $this->addSql('ALTER TABLE multimedia_record_multimedia_file DROP FOREIGN KEY FK_A489D14E2EC1C247');
        $this->addSql('ALTER TABLE individual_multimedia_records DROP FOREIGN KEY FK_FC9403B1673E5F6B');
        $this->addSql('ALTER TABLE multimedia_record_multimedia_file DROP FOREIGN KEY FK_A489D14E673E5F6B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7741D53CD');
        $this->addSql('ALTER TABLE source_data DROP FOREIGN KEY FK_AC7976605F8A7F73');
        $this->addSql('ALTER TABLE attribute DROP FOREIGN KEY FK_FA7AEFFB5F8A7F73');
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY FK_ADF3F3637E2A1954');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA75F8A7F73');
        $this->addSql('ALTER TABLE individual_source_data DROP FOREIGN KEY FK_21356B2A7E2A1954');
        $this->addSql('ALTER TABLE individual_name DROP FOREIGN KEY FK_96E1E4B55F8A7F73');
        $this->addSql('ALTER TABLE multimedia_record DROP FOREIGN KEY FK_2C7DA8615F8A7F73');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE data');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE family_events');
        $this->addSql('DROP TABLE gedcom');
        $this->addSql('DROP TABLE header');
        $this->addSql('DROP TABLE individual');
        $this->addSql('DROP TABLE individual_events');
        $this->addSql('DROP TABLE individual_source_data');
        $this->addSql('DROP TABLE individual_multimedia_records');
        $this->addSql('DROP TABLE individual_family');
        $this->addSql('DROP TABLE individual_name');
        $this->addSql('DROP TABLE multimedia_file');
        $this->addSql('DROP TABLE multimedia_record');
        $this->addSql('DROP TABLE multimedia_record_multimedia_file');
        $this->addSql('DROP TABLE place');
        $this->addSql('DROP TABLE source');
        $this->addSql('DROP TABLE source_data');
        $this->addSql('DROP TABLE user_reference');
    }
}
