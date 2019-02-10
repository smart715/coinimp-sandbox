<?php

namespace App\Twig;

use App\Communications\CurrencyConverterInterface;
use App\Utils\XMRConverter;
use PragmaRX\Random\Random;

class AppExtension extends \Twig_Extension
{
    /** @var CurrencyConverterInterface */
    private $currencyConverter;

    public function __construct(CurrencyConverterInterface $converter)
    {
        $this->currencyConverter = $converter;
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new \Twig_Function('random_string', array($this, 'randomString')),
        ];
    }

    /** {@inheritdoc} */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('toXMR', array($this, 'toXMR')),
            new \Twig_SimpleFilter('toUSD', array($this, 'toUSD')),
        ];
    }

    public function randomString(int $length): string
    {
        return (new Random())->size($length)->get();
    }

    public function toXMR(int $atomicUnits): string
    {
        return XMRConverter::toXMR($atomicUnits);
    }

    public function toUSD(int $units = 0): string
    {
        $xmr = (float)$this->toXMR($units);
        $usd = $this->currencyConverter->getRate(CurrencyConverterInterface::XMR, CurrencyConverterInterface::USD);
        return number_format(($xmr * $usd), 2, '.', '');
    }
}
