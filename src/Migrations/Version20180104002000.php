<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180104002000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE hashes_share (id INT AUTO_INCREMENT NOT NULL, site_id INT DEFAULT NULL, hashes BIGINT NOT NULL, fee DOUBLE PRECISION NOT NULL, INDEX IDX_AF633C4AF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hashes_share ADD CONSTRAINT FK_AF633C4AF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('INSERT INTO hashes_share (site_id, hashes, fee) SELECT id, hashes, 0.02 FROM site');

        $this->addSql('ALTER TABLE site DROP hashes');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site ADD hashes BIGINT NOT NULL');
        $this->addSql('
UPDATE site
LEFT JOIN 
(
  SELECT 
    site_id, 
    SUM(hashes) hashesSum
  FROM hashes_share
  GROUP BY site_id
) H 
ON site.id = H.site_id
SET site.hashes = COALESCE(H.hashesSum, 0)'
        );
        $this->addSql('DROP TABLE hashes_share');
    }
}
