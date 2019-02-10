<?php

namespace App\Notification;

use App\Notification\Fetcher\NotificationFetcherInterface;

class Notification implements NotificationInterface
{
    /** @var NotificationFetcherInterface $notificationFetcher */
    private $notificationFetcher;

    public const LEVELS = [
        ['number' => 1, 'name' => 'info'],
        ['number' => 2, 'name' => 'warning'],
        ['number' => 3, 'name' => 'alert'],
    ];

    public const DEFAULT_LEVEL_NUMBER = 2;

    public function __construct(NotificationFetcherInterface $notificationFetcher)
    {
        $this->notificationFetcher = $notificationFetcher;
    }

    public function hasNotification(): bool
    {
        return $this->notificationFetcher->hasNotification();
    }

    public function getLevelName(): string
    {
        foreach (self::LEVELS as $level)
            if ($this->notificationFetcher->getLevelNumber() === $level['number'])
                return $level['name'];

        return '';
    }

    public function getMessage(): string
    {
        return $this->notificationFetcher->getMessage();
    }

    public function getDefaultLevelName(): string
    {
        foreach (self::LEVELS as $level)
            if (self::DEFAULT_LEVEL_NUMBER === $level['number'])
                return $level['name'];

        throw new NotificationException('Unable to get notification default level name.');
    }

    public function convertLevelNameToNumber(string $levelName, bool $defaultLevelIfNotExists = true): int
    {
        foreach (self::LEVELS as $level)
            if ($levelName === $level['name'])
                return $level['number'];

        return $defaultLevelIfNotExists ? self::DEFAULT_LEVEL_NUMBER : 0;
    }

    public function levelNameIsValid(string $levelName): bool
    {
        return 0 !== $this->convertLevelNameToNumber($levelName, false);
    }
}
