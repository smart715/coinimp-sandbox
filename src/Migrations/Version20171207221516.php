<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171207221516 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, amount BIGINT NOT NULL, fee BIGINT NOT NULL, status ENUM(\'paid\', \'pending\', \'error\') NOT NULL COMMENT \'(DC2Type:PaymentStatus)\', `timestamp` DATETIME NOT NULL, INDEX IDX_6D28840DCCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('INSERT INTO craue_config_setting (name, `value`) VALUES(\'reward-fee\', 10000000000)'); // 0.01 XMR
        $this->addSql('UPDATE craue_config_setting SET name=\'minimal-block-reward\' WHERE name=\'minimal-reward\'');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment');
        $this->addSql('DELETE FROM craue_config_setting WHERE name=\'reward-fee\'');
        $this->addSql('UPDATE craue_config_setting SET name=\'minimal-reward\' WHERE name=\'minimal-block-reward\'');
    }
}
