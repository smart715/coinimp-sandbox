<?php

namespace App\Notification;

interface NotificationInterface
{
    public function hasNotification(): bool;
    public function getLevelName(): string;
    public function getMessage(): string;
    public function getDefaultLevelName(): string;
    public function convertLevelNameToNumber(string $levelName, bool $defaultLevelIfNotExists = true): int;
    public function levelNameIsValid(string $levelName): bool;
}
