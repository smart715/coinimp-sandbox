<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171204183607 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site ADD type INT NOT NULL');
        $this->addSql('UPDATE site SET type = 0 WHERE is_visible = 1');
        $this->addSql('UPDATE site SET type = 1 WHERE is_visible = 0');
        $this->addSql('ALTER TABLE site DROP is_visible');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM site WHERE (type > 1 OR type < 0)');
        $this->addSql('ALTER TABLE site ADD is_visible TINYINT(1) NOT NULL');
        $this->addSql('UPDATE site SET is_visible = 1 WHERE type = 0');
        $this->addSql('UPDATE site SET is_visible = 0 WHERE type = 1');
        $this->addSql('ALTER TABLE site DROP type');
    }
}
