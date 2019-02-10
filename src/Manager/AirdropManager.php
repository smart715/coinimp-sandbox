<?php

namespace App\Manager;

use App\Entity\Airdrop;
use App\Entity\Profile;
use App\Entity\WalletImp\WalletImp;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\AirdropRepository;
use App\Results\Airdrop\AirdropResult;
use App\WalletServices\WalletImpTransactionHandler;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;

class AirdropManager implements AirdropManagerInterface
{

    /** @var AirdropRepository */
    private $repository;

    /** @var OrmAdapterInterface */
    private $orm;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $airdropsProfileEmail;

    /** @var int */
    private $airdropsValue;

    /** @var WalletImpTransactionHandler */
    private $transactionHandler;

    public function __construct(
        WalletImpTransactionHandler $transactionHandler,
        OrmAdapterInterface $ormAdapter,
        LoggerInterface $logger,
        string $airdropsProfileEmail,
        int $airdropsValue
    ) {
        $this->transactionHandler = $transactionHandler;
        $this->logger = $logger;
        $this->orm = $ormAdapter;
        $this->repository = $this->getRepository();
        $this->airdropsProfileEmail = $airdropsProfileEmail;
        $this->airdropsValue = $airdropsValue;
    }

    public function addAirdrop(string $code, Profile $airdropsProfile, Profile $profile): AirdropResult
    {
        if ($this->hasAirdrops($profile)) {
            return new AirdropResult(AirdropResult::ALREADY_RECIEVED_AIRDROP);
        }
        $airdrop = $this->repository->findByCode($code);
        try {
            if (null !== $airdrop && $airdrop->getIsActive()) {
                $this->transactionHandler->moveAmountToUser($airdropsProfile, $profile, $this->airdropsValue * (10**WalletImp::impUnitBase), 'airdrops', 'add airdrops to user wallet');
                $airdrop->setProfile($profile);
                $airdrop->setIsActive(false);
                $this->orm->flush();
                return new AirdropResult(AirdropResult::SUCCESS, $this->airdropsValue);
            }
            $this->logger->error('this code ' . $code . ' is invalid or was already redeemed for profile ' . $profile->getEmail());
            return new AirdropResult(AirdropResult::INVALID_CODE);
        } catch (\Throwable $exception) {
            $this->logger->error('Exception while addAirdrop '.$exception->getMessage());
            return new AirdropResult(AirdropResult::FAILURE);
        }
    }

    public function getAirdropsProfileEmail(): string
    {
        return $this->airdropsProfileEmail;
    }

    public function hasAirdrops(Profile $profile): bool
    {
        return null !== $this->repository->findByProfile($profile);
    }

    public function getAirdropCode(): ?string
    {
        $airdrop = $this->repository->getFreeAirdrop();
        return $airdrop ? $airdrop->getCode() : null;
    }

    private function getRepository(): ObjectRepository
    {
        return $this->orm->getRepository(Airdrop::class);
    }
}
