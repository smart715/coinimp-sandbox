<?php

namespace App\Notification\Fetcher;

use Craue\ConfigBundle\Util\Config;

interface NotificationFetcherInterface
{
    public function hasNotification(): bool;
    public function getMessage(): string;
    public function getLevelNumber(): int;
    public function fetchNotification(): array;
}
