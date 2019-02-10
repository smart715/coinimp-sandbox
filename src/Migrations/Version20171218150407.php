<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171218150407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile ADD referencer_id INT DEFAULT NULL, ADD referral_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FF0D184D2 FOREIGN KEY (referencer_id) REFERENCES profile (id)');
        $this->addSql('CREATE INDEX IDX_8157AA0FF0D184D2 ON profile (referencer_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FF0D184D2');
        $this->addSql('DROP INDEX IDX_8157AA0FF0D184D2 ON profile');
        $this->addSql('ALTER TABLE profile DROP referencer_id, DROP referral_code');
    }
}
