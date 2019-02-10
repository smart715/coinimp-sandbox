<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171026203248 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username_canonical` VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_canonical` VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` TINYINT(1) NOT NULL,
  `salt` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `confirmation_token` VARCHAR(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_requested_at` DATETIME DEFAULT NULL,
  `roles` LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'(DC2Type:array)\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D64992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_8D93D649A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_8D93D649C05FB297` (`confirmation_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql(
'CREATE TABLE profile
(
  id             INT(11) NOT NULL AUTO_INCREMENT,
  user_id        INT(11) DEFAULT NULL,
  wallet_address VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8157AA0FA76ED395` (`user_id`),
  CONSTRAINT `FK_8157AA0FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql(
'CREATE TABLE site
(
  id         INT(11) NOT NULL AUTO_INCREMENT,
  profile_id INT(11) DEFAULT NULL,
  `name`     VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  mining_key VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_694309E4CCFA12B8` (`profile_id`),
  CONSTRAINT `FK_694309E4CCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`))
  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP table user');
    }
}
