<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180615225820 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE craue_config_setting SET name = 'xmr-network-cache-timestamp' WHERE name = 'network-cache-timestamp'");
        $this->addSql("UPDATE craue_config_setting SET name = 'xmr-network-cache-difficulty' WHERE name = 'network-cache-difficulty'");
        $this->addSql("UPDATE craue_config_setting SET name = 'xmr-network-cache-lastblockreward' WHERE name = 'network-cache-lastblockreward'");
        $this->addSql("UPDATE craue_config_setting SET name = 'xmr-minimal-block-reward' WHERE name = 'minimal-block-reward'");

        $this->addSql("INSERT INTO craue_config_setting (name, value) VALUES "
            ."('web-network-cache-timestamp', null), "
            ."('web-network-cache-difficulty', null), "
            ."('web-network-cache-lastblockreward', null), "
            ."('web-minimal-block-reward', '50000000000000')"
        );

    }

    public function down(Schema $schema) : void
    {
        $this->addSql("UPDATE craue_config_setting SET name = 'network-cache-timestamp' WHERE name = 'xmr-network-cache-timestamp'");
        $this->addSql("UPDATE craue_config_setting SET name = 'network-cache-difficulty' WHERE name = 'xmr-network-cache-difficulty'");
        $this->addSql("UPDATE craue_config_setting SET name = 'network-cache-lastblockreward' WHERE name = 'xmr-network-cache-lastblockreward'");
        $this->addSql("UPDATE craue_config_setting SET name = 'minimal-block-reward' WHERE name = 'xmr-minimal-block-reward'");

        $this->addSql("DELETE FROM craue_config_setting WHERE name IN ("
            ."'web-network-cache-timestamp', "
            ."'web-network-cache-difficulty', "
            ."'web-network-cache-lastblockreward', "
            ."'web-minimal-block-reward')"
        );
    }
}
