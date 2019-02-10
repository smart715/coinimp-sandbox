<?php

namespace App\Communications;

interface CurrencyConverterInterface
{
    public const XMR = 'XMR';
    public const WEB = 'WEB';
    public const BTC = 'BTC';
    public const ETH = 'ETH';
    public const USD = 'USD';
    public function getRate(string $from, string $to): float;
}
