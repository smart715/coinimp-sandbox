<?php

namespace App\Tests\Manager;

use App\Communications\CurrentTime;
use App\Entity\User;
use App\Manager\UserManager;
use DateTime;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserManagerTest extends TestCase
{
    public function testSendResettingEmail(): void
    {
        $token = 'token.abcdef';
        $isUserUpdated = false;
        $time = new DateTime('2000-01-01 00:00:00');

        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $tokenGenerator->method('generateToken')->willReturn($token);

        $user = $this->createMock(User::class);
        $user->method('setConfirmationToken')
            ->willReturnCallback(function ($token) use (&$user): void {
                $user->method('getConfirmationToken')->willReturn($token);
            });
        $user->method('setPasswordRequestedAt')
            ->willReturnCallback(function ($time) use (&$user): void {
                $user->method('getPasswordRequestedAt')->willReturn($time);
            });

        $baseUserManager = $this->createMock(UserManagerInterface::class);
        $baseUserManager->method('updateUser')
            ->willReturnCallback(function (User $user) use ($token, $time, &$isUserUpdated): void {
                $this->assertEquals($token, $user->getConfirmationToken());
                $this->assertEquals($time, $user->getPasswordRequestedAt());
                $isUserUpdated = true;
            });

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('sendResettingEmailMessage')
            ->with($this->equalTo($user))
            ->willReturnCallback(function (User $user) use (&$isUserUpdated): void {
                $this->assertTrue($isUserUpdated);
            });

        $currentTime = $this->createMock(CurrentTime::class);
        $logger = $this->createMock(LoggerInterface::class);
        $currentTime->method('getCurrentTime')->willReturn($time);
        (new UserManager(
            $baseUserManager,
            $tokenGenerator,
            $mailer,
            $logger,
            $currentTime
        ))->sendResettingEmail($user);
    }
}
