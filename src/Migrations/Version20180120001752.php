<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180120001752 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO craue_config_setting (name, `value`) VALUES "
            ."('network-cache-timestamp', null), "
            ."('network-cache-difficulty', null), "
            ."('network-cache-lastblockreward', null)"
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM craue_config_setting WHERE name IN ("
            ."'network-cache-timestamp', "
            ."'network-cache-difficulty', "
            ."'network-cache-lastblockreward')"
        );
    }
}
