<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210501064421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_snapshot DROP FOREIGN KEY FK_2A9A54D5296CD8AE');
        $this->addSql('DROP INDEX IDX_2A9A54D5296CD8AE ON player_snapshot');
        $this->addSql('ALTER TABLE player_snapshot DROP team_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_snapshot ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player_snapshot ADD CONSTRAINT FK_2A9A54D5296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_2A9A54D5296CD8AE ON player_snapshot (team_id)');
    }
}
