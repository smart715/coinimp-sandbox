<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ResettingSubscriber;
use FOS\UserBundle\Event\FormEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ResettingSubscriberTest extends TestCase
{
    public function testOnResetSuccess(): void
    {
        $router = $this->createMock(UrlGenerator::class);
        $router->method('generate')->willReturn('/profile');

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('setResponse')
            ->with(new RedirectResponse('/profile'));

        $resettingSubscriber = new ResettingSubscriber($router);
        $resettingSubscriber->onResetSuccess($event);
    }
}
