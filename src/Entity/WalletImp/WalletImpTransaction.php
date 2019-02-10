<?php

namespace App\Entity\WalletImp;

use App\Exception\Wallet\WrongWalletHistoryTypeException;
use DateTime;

class WalletImpTransaction
{
    const TYPE_ADD = 'add';
    const TYPE_SUB = 'sub';
    const TYPE_FREEZE = 'freeze';

    /** @var integer */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var integer */
    private $amount = 0;

    /** @var string */
    private $type = self::TYPE_ADD;

    /** @var WalletImp */
    private $wallet;

    /** @var array */
    private $data = [];

    /** @var DateTime */
    private $created;

    public function __construct(string $type = self::TYPE_ADD)
    {
        $this->setType($type);
        $this->created = new DateTime();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): WalletImpTransaction
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(?string $description): WalletImpTransaction
    {
        $this->description = $description;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): WalletImpTransaction
    {
        $this->amount = $amount;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): WalletImpTransaction
    {
        $types = [
            self::TYPE_ADD,
            self::TYPE_SUB,
            self::TYPE_FREEZE
        ];
        if (!in_array($type, $types)) {
            throw new WrongWalletHistoryTypeException('WrongWalletHistoryType');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string[]
     */

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string[] $data
     * @return WalletImpTransaction
     */
    public function setData(array $data = []): WalletImpTransaction
    {
        $this->data = $data;
        return $this;
    }

    public function getWallet(): WalletImp
    {
        return $this->wallet;
    }

    public function setWallet(WalletImp $wallet): WalletImpTransaction
    {
        $this->wallet = $wallet;
        return $this;
    }
}
