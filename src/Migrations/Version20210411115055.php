<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210411115055 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, nom TINYTEXT NOT NULL, etat INT DEFAULT NULL, user_id INT NOT NULL, partie_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, valeur INT NOT NULL, image TINYTEXT NOT NULL, objectif_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat (message_id INT AUTO_INCREMENT NOT NULL, user_pseudo TINYTEXT NOT NULL, message TINYTEXT NOT NULL, message_heure DATETIME NOT NULL, PRIMARY KEY(message_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objectif (id INT AUTO_INCREMENT NOT NULL, valeur INT DEFAULT NULL, image TINYTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partie (id INT AUTO_INCREMENT NOT NULL, tour_id INT DEFAULT NULL, carte_rejected INT NOT NULL, j1_id INT NOT NULL, j2_id INT NOT NULL, main_j1 TEXT NOT NULL, main_j2 TEXT NOT NULL, carte_placed_j1 TEXT NOT NULL, carte_placed_j2 TEXT NOT NULL, score_j1 INT NOT NULL, score_j2 INT NOT NULL, manche INT NOT NULL, pioche TEXT NOT NULL, objectifs TEXT NOT NULL, actions_j1 TEXT NOT NULL, actions_j2 TEXT NOT NULL, INDEX IDX_59B1F3D15ED8D43 (tour_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, username VARCHAR(25) NOT NULL, password VARCHAR(255) NOT NULL, parties_played INT NOT NULL, parties_win INT NOT NULL, parties_loose INT NOT NULL, score NUMERIC(10, 2) NOT NULL, friends TEXT DEFAULT NULL, ban_statut INT DEFAULT NULL, chat_ban_tenta INT DEFAULT NULL, chat_ban_statut INT DEFAULT NULL, admin INT DEFAULT NULL, roles VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3D15ED8D43 FOREIGN KEY (tour_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3D15ED8D43');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE objectif');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE user');
    }
}
