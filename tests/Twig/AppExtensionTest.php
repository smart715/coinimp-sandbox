<?php

namespace App\Tests\Twig;

use App\Communications\CurrencyConverterInterface;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use Twig_Function;
use Twig_SimpleFilter;

class AppExtensionTest extends TestCase
{
    /** @var AppExtension */
    private $appExtension;

    public function setUp(): void
    {
        $this->appExtension = new AppExtension($this->mockCurrencyConverter(2.0));
    }

    public function testGetFunctions(): void
    {
        $functions = $this->appExtension->getFunctions();
        foreach ($functions as $function)
            $this->assertInstanceOf(Twig_Function::class, $function);
    }

    public function testGetFilters(): void
    {
        $filters = $this->appExtension->getFilters();
        foreach ($filters as $filter)
            $this->assertInstanceOf(Twig_SimpleFilter::class, $filter);
    }

    public function testRandomString(): void
    {
        $randomString = $this->appExtension->randomString(20);
        $this->assertEquals(20, strlen($randomString));
    }

    public function testToXMR(): void
    {
        $toXMR = $this->appExtension->toXMR(500000000);
        $this->assertEquals('0.00050000', $toXMR);
    }

    public function testToUSD(): void
    {
        $toUSD = $this->appExtension->toUSD(5000000000);
        $this->assertEquals('0.01', $toUSD);
    }

    private function mockCurrencyConverter(float $rate): CurrencyConverterInterface
    {
        $converter = $this->createMock(CurrencyConverterInterface::class);
        $converter->method('getRate')->willReturn($rate);
        return $converter;
    }
}
