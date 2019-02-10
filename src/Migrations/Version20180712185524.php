<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180712185524 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE wallet_imp (id INT AUTO_INCREMENT NOT NULL, total_amount BIGINT NOT NULL, freeze_amount INT NOT NULL, actual_paid BIGINT NOT NULL, created DATETIME default CURRENT_TIMESTAMP, updated DATETIME on update CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet_imp_transaction (id INT AUTO_INCREMENT NOT NULL, wallet_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, amount BIGINT NOT NULL, type ENUM(\'add\', \'sub\', \'freeze\'), data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME default CURRENT_TIMESTAMP, INDEX IDX_D799E7B8712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wallet_imp_transaction ADD CONSTRAINT FK_D799E7B8712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet_imp (id)');
        $this->addSql('ALTER TABLE profile ADD wallet_imp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FBD046F37 FOREIGN KEY (wallet_imp_id) REFERENCES wallet_imp (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0FBD046F37 ON profile (wallet_imp_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FBD046F37');
        $this->addSql('ALTER TABLE wallet_imp_transaction DROP FOREIGN KEY FK_D799E7B8712520F3');
        $this->addSql('DROP TABLE wallet_imp');
        $this->addSql('DROP TABLE wallet_imp_transaction');
        $this->addSql('DROP INDEX UNIQ_8157AA0FBD046F37 ON profile');
        $this->addSql('ALTER TABLE profile DROP wallet_imp_id');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->executeQuery(
            'CREATE PROCEDURE create_wallets()
             BEGIN
                INSERT INTO `user` 
                (`username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`) 
                VALUES (\'airdrops\', \'airdrops\', \'airdrops\', \'airdrops\', \'0\', \'\', \'a:1:{i:0;s:9:"ROLE_USER";}\');
                SET @uid := LAST_INSERT_ID();
                INSERT INTO wallet_imp (total_amount, freeze_amount, actual_paid) Values(25000000 * POW(10, 8) , 0, 0);
                SET @wallet_id := LAST_INSERT_ID();
                INSERT INTO profile (user_id, wallet_imp_id, referral_code) Values(@uid, @wallet_id, \'\');
                
                INSERT INTO `user` 
                (`username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`) 
                VALUES (\'tokensale\', \'tokensale\', \'tokensale\', \'tokensale\', \'0\', \'\' , \'a:1:{i:0;s:9:"ROLE_USER";}\');
                SET @uid := LAST_INSERT_ID();
                INSERT INTO wallet_imp (total_amount, freeze_amount, actual_paid) Values(320000000 * POW(10, 8), 0, 0);
                SET @wallet_id := LAST_INSERT_ID();
                INSERT INTO profile (user_id, wallet_imp_id, referral_code) Values(@uid, @wallet_id, \'\');
             END;
        ');
        $this->connection->executeQuery('CALL create_wallets;');
        $this->connection->executeQuery('DROP PROCEDURE IF EXISTS create_wallets;');
    }

}
