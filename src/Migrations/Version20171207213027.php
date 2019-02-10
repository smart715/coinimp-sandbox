<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171207213027 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site ADD reward_backup DOUBLE PRECISION NOT NULL');
        $this->addSql('UPDATE site SET reward_backup = reward');
        $this->addSql('ALTER TABLE site CHANGE reward reward BIGINT NOT NULL');
        $this->addSql('UPDATE site SET reward = ROUND(reward_backup * POW(10, 12))');
        $this->addSql('ALTER TABLE site DROP reward_backup');
        $this->addSql('UPDATE craue_config_setting SET `value` = ROUND(`value` * POW(10, 12))  WHERE name=\'minimal-reward\'');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site ADD reward_backup BIGINT NOT NULL');
        $this->addSql('UPDATE site SET reward_backup = reward');
        $this->addSql('ALTER TABLE site CHANGE reward reward DOUBLE PRECISION NOT NULL');
        $this->addSql('UPDATE site SET reward = reward_backup / POW(10, 12)');
        $this->addSql('ALTER TABLE site DROP reward_backup');
        $this->addSql('UPDATE craue_config_setting SET `value` = `value` / POW(10, 12) WHERE name=\'minimal-reward\'');
    }
}
