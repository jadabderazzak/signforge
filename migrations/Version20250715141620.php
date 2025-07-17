<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715141620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_item (id INT AUTO_INCREMENT NOT NULL, document_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, discount SMALLINT DEFAULT NULL, taxe SMALLINT NOT NULL, total DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B8AFA98DC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_item ADD CONSTRAINT FK_B8AFA98DC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document_item DROP FOREIGN KEY FK_B8AFA98DC33F7837');
        $this->addSql('DROP TABLE document_item');
    }
}
