<?php


namespace App\Entity\Payment;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class Status extends AbstractEnumType
{
    public const PAID = 'paid';
    public const PENDING = 'pending';
    public const ERROR = 'error';

    protected static $choices = [
        self::PAID => 'Paid',
        self::PENDING => 'Pending',
        self::ERROR => 'Error',
    ];
}