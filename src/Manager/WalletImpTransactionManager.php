<?php

namespace App\Manager;

use App\Entity\Profile;
use App\Entity\WalletImp\WalletImp;
use App\Entity\WalletImp\WalletImpTransaction;
use Doctrine\ORM\EntityManagerInterface;

class WalletImpTransactionManager implements WalletImpTransactionManagerInterface
{
    /** @var EntityManagerInterface */
    private $orm;

    public function __construct(EntityManagerInterface $orm)
    {
        $this->orm = $orm;
    }

    public function addWalletTransaction(
        WalletImp $wallet,
        string $type,
        float $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $transaction = new WalletImpTransaction($type);
        $transaction->setAmount($amount);
        $transaction->setName($name);
        $transaction->setDescription($description);
        $transaction->setData($data);
        $wallet->addTransaction($transaction);
    }

    public function getProfileTransactions(Profile $profile): array
    {
        $repository = $this->orm->getRepository(WalletImpTransaction::class);
        return $repository->findBy(['wallet' => $profile->getWalletImp()]);
    }
}
