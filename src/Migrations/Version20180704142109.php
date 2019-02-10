<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180704142109 extends AbstractMigration
{
    public function up(Schema $schema) :void
    {
        $this->addSql("INSERT INTO craue_config_setting (name, `value`) VALUES "
            . "('notification', null)"
        );
    }

    public function down(Schema $schema) :void
    {
        $this->addSql("DELETE FROM craue_config_setting WHERE name = 'notification'");
    }
}