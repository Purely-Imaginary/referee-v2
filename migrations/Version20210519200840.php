<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210519200840 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `calculated_match` (id INT AUTO_INCREMENT NOT NULL, time VARCHAR(255) DEFAULT NULL, start_time DOUBLE PRECISION DEFAULT NULL, end_time DOUBLE PRECISION DEFAULT NULL, raw_positions VARCHAR(1000) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE goal (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, calculated_match_id INT DEFAULT NULL, time DOUBLE PRECISION DEFAULT NULL, travel_time DOUBLE PRECISION DEFAULT NULL, speed DOUBLE PRECISION DEFAULT NULL, shot_time DOUBLE PRECISION DEFAULT NULL, is_red TINYINT(1) NOT NULL, INDEX IDX_FCDCEB2E99E6F5DF (player_id), INDEX IDX_FCDCEB2E901D0D7A (calculated_match_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, wins INT DEFAULT 0 NOT NULL, losses INT DEFAULT 0 NOT NULL, goals_scored INT DEFAULT 0 NOT NULL, goals_lost INT DEFAULT 0 NOT NULL, rating DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_snapshot (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, team_snapshot_id INT DEFAULT NULL, rating DOUBLE PRECISION DEFAULT NULL, is_red TINYINT(1) NOT NULL, INDEX IDX_2A9A54D599E6F5DF (player_id), INDEX IDX_2A9A54D59009D613 (team_snapshot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE raw_match (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, avg_team_rating DOUBLE PRECISION NOT NULL, score INT NOT NULL, rating_change DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_snapshot (id INT AUTO_INCREMENT NOT NULL, calculated_match_id INT DEFAULT NULL, score INT NOT NULL, rating_change DOUBLE PRECISION DEFAULT NULL, is_red TINYINT(1) NOT NULL, INDEX IDX_940FC94B901D0D7A (calculated_match_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE test (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E901D0D7A FOREIGN KEY (calculated_match_id) REFERENCES `calculated_match` (id)');
        $this->addSql('ALTER TABLE player_snapshot ADD CONSTRAINT FK_2A9A54D599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE player_snapshot ADD CONSTRAINT FK_2A9A54D59009D613 FOREIGN KEY (team_snapshot_id) REFERENCES team_snapshot (id)');
        $this->addSql('ALTER TABLE team_snapshot ADD CONSTRAINT FK_940FC94B901D0D7A FOREIGN KEY (calculated_match_id) REFERENCES `calculated_match` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2E901D0D7A');
        $this->addSql('ALTER TABLE team_snapshot DROP FOREIGN KEY FK_940FC94B901D0D7A');
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2E99E6F5DF');
        $this->addSql('ALTER TABLE player_snapshot DROP FOREIGN KEY FK_2A9A54D599E6F5DF');
        $this->addSql('ALTER TABLE player_snapshot DROP FOREIGN KEY FK_2A9A54D59009D613');
        $this->addSql('DROP TABLE `calculated_match`');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE player_snapshot');
        $this->addSql('DROP TABLE raw_match');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_snapshot');
        $this->addSql('DROP TABLE test');
    }
}
