<?php

namespace App\Tests\Notification;

use App\Notification\Fetcher\NotificationFetcher;
use Craue\ConfigBundle\Util\Config;
use PHPUnit\Framework\TestCase;

class NotificationFetcherTest extends TestCase
{
    public function testNotificationFetcher(): void
    {
        $message = 'CoinIMP is GREAT';
        $levelNumber = 2;

        $notificationFetcher = $this->createNotificationFetcher($message, $levelNumber);

        $this->assertEquals(true, $notificationFetcher->hasNotification());
        $this->assertEquals($message, $notificationFetcher->getMessage());
        $this->assertEquals($levelNumber, $notificationFetcher->getLevelNumber());
    }

    private function createNotificationFetcher(string $message, int $levelNumber): NotificationFetcher
    {
        return new NotificationFetcher($this->createConfigMockWithMessage(
            $message,
            $levelNumber
        ));
    }

    private function createConfigMockWithMessage(string $message, int $levelNumber): Config
    {
        $configMock = $this->createMock(Config::class);

        $configMock
            ->expects($this->exactly(6))
            ->method('get')
            ->with('notification')
            ->willReturn(serialize([
                $message,
                $levelNumber,
            ]))
        ;

        return $configMock;
    }
}
