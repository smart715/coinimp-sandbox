<?php

namespace App\Notification\Fetcher;

use Craue\ConfigBundle\Util\Config;

class NotificationFetcher implements NotificationFetcherInterface
{
    /** @var Config $config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function hasNotification(): bool
    {
        return !empty($this->getMessage());
    }

    public function getMessage(): string
    {
        return $this->fetchNotification()[0] ?? '';
    }

    public function getLevelNumber(): int
    {
        return $this->fetchNotification()[1] ?? '';
    }

    public function fetchNotification(): array
    {
        return $this->config->get('notification')
            ? unserialize($this->config->get('notification'))
            : [];
    }
}
