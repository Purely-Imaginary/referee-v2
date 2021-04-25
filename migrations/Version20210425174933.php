<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210425174933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player DROP win_rate, CHANGE wins wins INT DEFAULT 0 NOT NULL, CHANGE losses losses INT DEFAULT 0 NOT NULL, CHANGE goals_shot goals_shot INT DEFAULT 0 NOT NULL, CHANGE goals_scored goals_scored INT DEFAULT 0 NOT NULL, CHANGE goals_lost goals_lost INT DEFAULT 0 NOT NULL, CHANGE rating rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE team_snapshot DROP FOREIGN KEY FK_940FC94B257F118');
        $this->addSql('DROP INDEX IDX_940FC94B257F118 ON team_snapshot');
        $this->addSql('ALTER TABLE team_snapshot ADD team_color VARCHAR(255) NOT NULL, DROP avg_team_rating, CHANGE red_calculated_match_id calculated_match_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_snapshot ADD CONSTRAINT FK_940FC94B901D0D7A FOREIGN KEY (calculated_match_id) REFERENCES `calculated_match` (id)');
        $this->addSql('CREATE INDEX IDX_940FC94B901D0D7A ON team_snapshot (calculated_match_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player ADD win_rate DOUBLE PRECISION NOT NULL, CHANGE wins wins INT NOT NULL, CHANGE losses losses INT NOT NULL, CHANGE goals_shot goals_shot INT NOT NULL, CHANGE goals_scored goals_scored INT NOT NULL, CHANGE goals_lost goals_lost INT NOT NULL, CHANGE rating rating DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE team_snapshot DROP FOREIGN KEY FK_940FC94B901D0D7A');
        $this->addSql('DROP INDEX IDX_940FC94B901D0D7A ON team_snapshot');
        $this->addSql('ALTER TABLE team_snapshot ADD avg_team_rating DOUBLE PRECISION NOT NULL, DROP team_color, CHANGE calculated_match_id red_calculated_match_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team_snapshot ADD CONSTRAINT FK_940FC94B257F118 FOREIGN KEY (red_calculated_match_id) REFERENCES calculated_match (id)');
        $this->addSql('CREATE INDEX IDX_940FC94B257F118 ON team_snapshot (red_calculated_match_id)');
    }
}
