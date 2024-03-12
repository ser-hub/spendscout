<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240125135711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, tag_id INT NOT NULL, currency_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, is_expense TINYINT(1) NOT NULL, amount DOUBLE PRECISION NOT NULL, date DATE NOT NULL, INDEX IDX_2B219D70A76ED395 (user_id), INDEX IDX_2B219D70BAD26311 (tag_id), INDEX IDX_2B219D7038248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D70A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D70BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D7038248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D70A76ED395');
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D70BAD26311');
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D7038248176');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783A76ED395');
        $this->addSql('DROP TABLE entry');
        $this->addSql('DROP TABLE tag');
    }
}
