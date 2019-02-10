<?php

namespace App\Tests\Service;

use App\Entity\Profile;
use App\Entity\WalletImp\WalletImpTransaction;
use App\Exception\Wallet\NotEnoughBalanceException;
use App\WalletServices\WalletImpTransactionHandler;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionTest extends KernelTestCase
{
    /** @var WalletImpTransactionHandler */
    private $walletImpTransactionHandler;
    /** @var UserInterface */
    private $user;
    /** @var  Profile */
    private $profile;
    /** @var UserInterface */
    private $fromUser;
    /** @var UserInterface */
    private $toUser;
    /** @var Profile */
    private $fromProfile;
    /** @var Profile */
    private $toProfile;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
    }
    public function setup(): void
    {
        $this->walletImpTransactionHandler = static::$kernel->getContainer()->get('walletImp.transaction.handler');
        $this->user = $this->createUser();
        $this->profile = $this->createProfile($this->user->getId());
        $this->fromUser = $this->createUser('user1');
        $this->fromProfile = $this->createProfile($this->fromUser->getId());
        $this->toUser = $this->createUser('user2');
        $this->toProfile = $this->createProfile($this->toUser->getId());
    }

    protected function tearDown(): void
    {
        $profileManager = static::$kernel->getContainer()->get('profile_manager');
        $profileManager->deleteProfile($this->profile);
        $profileManager->deleteProfile($this->fromProfile);
        $profileManager->deleteProfile($this->toProfile);

        /** @var UserManager $userManager */
        $userManager = static::$kernel->getContainer()->get('fos_user.user_manager');
        $userManager->deleteUser($this->user);
        $userManager->deleteUser($this->toUser);
        $userManager->deleteUser($this->fromUser);
    }

    protected function createUser(string $username = 'test', string $password = 'test'): UserInterface
    {
        /** @var UserManager $userManager */
        $userManager = static::$kernel->getContainer()->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($username . '@test.com');
        $user->setPlainPassword($password);
        $user->setEnabled(true);
        $userManager->updateUser($user);
        return $user;
    }

    protected function createProfile(string $userId): Profile
    {
        $profileManager = static::$kernel->getContainer()->get('profile_manager');
        $profile = $profileManager->createProfile($userId);
        return $profile;
    }

    public function testProfileHasWallet(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->assertNotNull($wallet);
        $this->assertEquals(0, $wallet->getAvailableAmount());
        $this->assertEquals(0, $wallet->getFreezeAmount());
        $this->assertEquals(0, $wallet->getTotalAmount());
    }

    public function testAddToWallet(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->profile, 199, 'testTrans');
        $this->assertEquals(199, $wallet->getTotalAmount());
        $this->assertEquals(199, $wallet->getAvailableAmount());
        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletImpTransaction::TYPE_ADD, $lastTransaction->getType());
        $this->assertEquals('testTrans', $lastTransaction->getName());
        $this->assertEquals(199, $lastTransaction->getAmount());
    }

    public function testTwiceAddToWallet(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->profile, 199, 'add1');
        $this->walletImpTransactionHandler->addToUser($this->profile, 249, 'add2', 'addAll');
        $this->assertEquals(448, $wallet->getTotalAmount());
        $this->assertEquals(448, $wallet->getAvailableAmount());
        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletImpTransaction::TYPE_ADD, $lastTransaction->getType());
        $this->assertEquals('add2', $lastTransaction->getName());
        $this->assertEquals(249, $lastTransaction->getAmount());
        $this->assertEquals('addAll', $lastTransaction->getDescription());
    }
    public function testSubFromWallet(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->profile, 600, 'add');
        $this->walletImpTransactionHandler->subFromUser($this->profile, 400, 'sub');
        $this->assertEquals(200, $wallet->getTotalAmount());
        $this->assertEquals(200, $wallet->getAvailableAmount());
        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletImpTransaction::TYPE_SUB, $lastTransaction->getType());
        $this->assertEquals('sub', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testSubFromWalletWithFreeze(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->profile, 600, 'add');
        $this->walletImpTransactionHandler->freezeFromUser($this->profile, 50, 'freeze');
        $this->walletImpTransactionHandler->subFromUser($this->profile, 400, 'sub');
        $this->assertEquals(200, $wallet->getTotalAmount());
        $this->assertEquals(150, $wallet->getAvailableAmount());
        $this->assertEquals(50, $wallet->getFreezeAmount());
        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletImpTransaction::TYPE_SUB, $lastTransaction->getType());
        $this->assertEquals('sub', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testMoveFreezeFromUserToUser(): void
    {
        $fromWallet = $this->fromProfile->getWalletImp();
        $toWallet = $this->toProfile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->fromProfile, 600, 'add');
        $this->walletImpTransactionHandler->freezeFromUser($this->fromProfile, 400, 'freeze');
        $this->assertEquals(200, $fromWallet->getAvailableAmount());
        $this->assertEquals(600, $fromWallet->getTotalAmount());
        $this->assertEquals(400, $fromWallet->getFreezeAmount());
        $this->assertEquals(0, $toWallet->getAvailableAmount());
        $this->walletImpTransactionHandler->moveFreezeAmountToUser($this->fromProfile, $this->toProfile, 400, 'moveFreeze');
        $this->assertEquals(400, $toWallet->getAvailableAmount());
        $this->assertEquals(400, $toWallet->getTotalAmount());
        $this->assertEquals(0, $toWallet->getFreezeAmount());
        $this->assertEquals(200, $fromWallet->getAvailableAmount());
        $this->assertEquals(200, $fromWallet->getTotalAmount());
        $this->assertEquals(0, $fromWallet->getFreezeAmount());
    }

    public function testMoveFromUserToUser(): void
    {
        $fromWallet = $this->fromProfile->getWalletImp();
        $toWallet = $this->toProfile->getWalletImp();
        $this->walletImpTransactionHandler->addToUser($this->fromProfile, 600, 'add');
        $this->assertEquals(600, $fromWallet->getTotalAmount());
        $this->assertEquals(0, $toWallet->getAvailableAmount());
        $this->walletImpTransactionHandler->moveAmountToUser($this->fromProfile, $this->toProfile, 400, 'move');
        $this->assertEquals(400, $toWallet->getAvailableAmount());
        $this->assertEquals(400, $toWallet->getTotalAmount());
        $this->assertEquals(0, $toWallet->getFreezeAmount());
        $this->assertEquals(200, $fromWallet->getAvailableAmount());
        $this->assertEquals(200, $fromWallet->getTotalAmount());
        $this->assertEquals(0, $fromWallet->getFreezeAmount());
    }

    public function testSubFromWalletWithNotEnoughMoney(): void
    {
        $this->expectException(NotEnoughBalanceException::class);
        $this->walletImpTransactionHandler->subFromUser($this->profile, 400, 'bar');
    }

    public function testAddToActualPaid(): void
    {
        $wallet = $this->profile->getWalletImp();
        $this->walletImpTransactionHandler->addToActualPaid($this->profile, 1);
        $this->assertEquals(1, $wallet->getActualPaid());
        $this->walletImpTransactionHandler->addToActualPaid($this->profile, 1);
        $this->assertEquals(2, $wallet->getActualPaid());
    }
}
