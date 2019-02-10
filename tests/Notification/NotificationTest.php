<?php

namespace App\Tests\Notification;

use App\Notification\Fetcher\NotificationFetcher;
use App\Notification\Notification;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotification(): void
    {
        $notification = $this->createNotificationWithMessage('CoinIMP is GREAT', 2);

        $this->assertEquals('CoinIMP is GREAT', $notification->getMessage());
        $this->assertEquals('warning', $notification->getLevelName());

        $defaultLevelNames = array_column(Notification::LEVELS, 'name');
        $this->assertTrue(in_array($notification->getDefaultLevelName(), $defaultLevelNames));
    }

    private function createNotificationWithMessage(
        string $message,
        int $levelNumber
    ): Notification {

        return new Notification(
            $this->createNotificationFetcherMockWithMessage(
                $message,
                $levelNumber
            )
        );
    }

    private function createNotificationFetcherMockWithMessage(
        string $message,
        int $levelNumber
    ): NotificationFetcher {

        $notificationFetcherMock = $this->createMock(NotificationFetcher::class);

        $notificationFetcherMock
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($message)
        ;

        $notificationFetcherMock
            ->expects($this->exactly(2))
            ->method('getLevelNumber')
            ->willReturn($levelNumber)
        ;

        return $notificationFetcherMock;
    }
}
