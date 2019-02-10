<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180702193114 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE crypto (id INT AUTO_INCREMENT NOT NULL, symbol VARCHAR(255) NOT NULL, minimal_payout DOUBLE PRECISION NOT NULL, fee DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT INTO crypto(symbol, minimal_payout, fee) VALUE ("xmr", 0.2, 0.02)');

        $this->addSql('CREATE TABLE preserve (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, crypto_id INT DEFAULT NULL, reward BIGINT NOT NULL, hashes BIGINT NOT NULL, referral_reward BIGINT NOT NULL, INDEX IDX_4BFE7BA5CCFA12B8 (profile_id), INDEX IDX_4BFE7BA5E9571A63 (crypto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE preserve ADD CONSTRAINT FK_4BFE7BA5CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE preserve ADD CONSTRAINT FK_4BFE7BA5E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('INSERT INTO preserve (profile_id, crypto_id, reward, hashes, referral_reward) SELECT profile.id, crypto.id, profile.preserved_reward, profile.preserved_hashes, profile.preserved_referral_reward FROM profile, crypto WHERE crypto.symbol = \'xmr\'');

        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, crypto_id INT DEFAULT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_7C68921FCCFA12B8 (profile_id), INDEX IDX_7C68921FE9571A63 (crypto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FE9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('INSERT INTO wallet (profile_id, crypto_id, address) SELECT profile.id, crypto.id, profile.wallet_address FROM profile, crypto WHERE crypto.symbol = \'xmr\'');

        $this->addSql('ALTER TABLE profile DROP wallet_address, DROP preserved_reward, DROP preserved_hashes, DROP preserved_referral_reward');

        $this->addSql('ALTER TABLE site ADD crypto_id INT DEFAULT NULL, DROP preserved_hashes');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('CREATE INDEX IDX_694309E4E9571A63 ON site (crypto_id)');
        $this->addSql('UPDATE site, crypto SET site.crypto_id = crypto.id WHERE crypto.symbol = \'xmr\'');

        $this->addSql('ALTER TABLE payment ADD crypto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DE9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id)');
        $this->addSql('CREATE INDEX IDX_6D28840DE9571A63 ON payment (crypto_id)');
        $this->addSql('UPDATE payment, crypto SET payment.crypto_id = crypto.id WHERE crypto.symbol = \'xmr\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile ADD wallet_address VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD preserved_reward BIGINT NOT NULL, ADD preserved_hashes BIGINT NOT NULL, ADD preserved_referral_reward BIGINT NOT NULL');
        $this->addSql('UPDATE profile LEFT JOIN preserve ON profile.id = preserve.profile_id LEFT JOIN crypto ON preserve.crypto_id = crypto.id SET profile.preserved_reward = preserve.reward, profile.preserved_hashes = preserve.hashes, profile.preserved_referral_reward = preserve.referral_reward WHERE crypto.symbol = \'xmr\'');
        $this->addSql('ALTER TABLE preserve DROP FOREIGN KEY FK_4BFE7BA5E9571A63');
        $this->addSql('DROP TABLE preserve');
        $this->addSql('UPDATE profile LEFT JOIN wallet ON profile.id = wallet.profile_id LEFT JOIN crypto ON wallet.crypto_id = crypto.id SET profile.wallet_address = wallet.address WHERE crypto.symbol = \'xmr\'');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FE9571A63');
        $this->addSql('DROP TABLE wallet');

        $this->addSql('DELETE site FROM site LEFT JOIN crypto ON site.crypto_id = crypto.id WHERE crypto.symbol != \'xmr\'');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4E9571A63');
        $this->addSql('DELETE payment FROM payment LEFT JOIN crypto ON payment.crypto_id = crypto.id WHERE crypto.symbol != \'xmr\'');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DE9571A63');
        $this->addSql('DROP TABLE crypto');
        $this->addSql('DROP INDEX IDX_6D28840DE9571A63 ON payment');
        $this->addSql('ALTER TABLE payment DROP crypto_id');
        $this->addSql('DROP INDEX IDX_694309E4E9571A63 ON site');
        $this->addSql('ALTER TABLE site ADD preserved_hashes BIGINT NOT NULL, DROP crypto_id');
    }
}