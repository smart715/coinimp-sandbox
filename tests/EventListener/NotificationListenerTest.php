<?php

namespace App\Tests\EventListener;

use App\EventListener\NotificationListener;
use App\Notification\Notification;
use Craue\ConfigBundle\Util\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotificationListenerTest extends TestCase
{
    public function testOnKernelRequest(): void
    {
        $notificationListener = new NotificationListener(
            $this->createFlashBagMock(),
            $this->createNotificationMockWithMessage('We always CAN!', 'info'),
            $this->createTokenStorageMock(),
            $this->createAuthorizationMock()
        );
        $notificationListener->onKernelRequest($this->createGetResponseEventMock());
    }

    private function createNotificationMockWithMessage(
        string $message,
        string $levelName
    ): Notification {

        $notificationMock = $this->createMock(Notification::class);

        $notificationMock
            ->expects($this->once())
            ->method('hasNotification')
            ->willReturn(true)
        ;
        $notificationMock
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($message)
        ;
        $notificationMock
            ->expects($this->once())
            ->method('getLevelName')
            ->willReturn($levelName)
        ;

        return $notificationMock;
    }

    private function createRequestMock(): Request
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('dashboard')
        ;
        return $requestMock;
    }

    private function createFlashBagMock(): FlashBagInterface
    {
        $flashBagMock = $this->createMock(FlashBagInterface::class);
        $flashBagMock->expects($this->once())
            ->method('set')
        ;
        return $flashBagMock;
    }

    private function createGetResponseEventMock(): GetResponseEvent
    {
        $getResponseEvent = $this->createMock(GetResponseEvent::class);
        $getResponseEvent->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->createRequestMock());
        ;
        return $getResponseEvent;
    }

    private function createTokenStorageMock(): TokenStorageInterface
    {
        $token = $this->createMock(TokenStorageInterface::class);
        $token->expects($this->once())
            ->method('getToken')
            ->willReturn(true);
        return $token;
    }

    private function createAuthorizationMock(): AuthorizationCheckerInterface
    {
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        return $authorization;
    }
}
