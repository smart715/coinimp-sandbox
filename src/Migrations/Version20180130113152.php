<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180130113152 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // FOSUserBundle doesn't save ROLE_USER (default role) in db
        // This changes existent users to have ROLE_USER by default in db, so we can filter by ROLE_USER in support panel
        $this->addSql('UPDATE user SET roles = ? WHERE roles = ?', ['a:1:{i:0;s:9:"ROLE_USER";}', 'a:0:{}']);
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE user SET roles = ? WHERE roles = ?', ['a:0:{}', 'a:1:{i:0;s:9:"ROLE_USER";}']);
    }
}
