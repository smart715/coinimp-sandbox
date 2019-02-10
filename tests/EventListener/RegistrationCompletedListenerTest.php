<?php

namespace App\Tests\EventListener;

use App\Entity\Profile;
use App\Entity\User;
use App\EventListener\RegistrationCompletedListener;
use App\Manager\ProfileManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class RegistrationCompletedListenerTest extends TestCase
{
    private const USER_ID = 42;
    private const REFERRAL_CODE = '550e8400-e29b-41d4-a716-446655440000';

    /**
     * @dataProvider referralRequestsProvider
     * @param Request $request
     */
    public function testItDoesCreateReferredProfile(Request $request): void
    {
        $profileManager = $this->createProfileManagerWithoutProfile();
        $profileManager->expects($this->once())
            ->method('createProfileReferral')
            ->with(self::USER_ID, self::REFERRAL_CODE);
        $profileManager->expects($this->never())->method('createProfile');

        $listener = new RegistrationCompletedListener($profileManager);

        $listener->onFosuserRegistrationCompleted($this->createEvent($request));
    }

    public function testItDoesCreateNonReferredProfileIfNoReferralCode(): void
    {
        $profileManager = $this->createProfileManagerWithoutProfile();
        $profileManager->expects($this->never())->method('createProfileReferral');
        $profileManager->expects($this->once())
            ->method('createProfile')
            ->with(self::USER_ID);

        $listener = new RegistrationCompletedListener($profileManager);

        $listener->onFosuserRegistrationCompleted(
            $this->createEvent($this->createRequestNoCode())
        );
    }

    public function testItDoesNotCreateProfileIfProfileExists(): void
    {
        $profileManager = $this->createProfileManagerWithProfile();
        $profileManager->expects($this->never())->method('createProfileReferral');
        $profileManager->expects($this->never())->method('createProfile');

        $listener = new RegistrationCompletedListener($profileManager);

        $listener->onFosuserRegistrationCompleted(
            $this->createEvent($this->createRequestCodeInCookies())
        );
    }

    public function referralRequestsProvider(): array
    {
        return [
            'referral code in cookie' => [ $this->createRequestCodeInCookies() ],
            'referral code in session' => [ $this->createRequestCodeInSession() ],
        ];
    }

    /**
     * @return ProfileManagerInterface|MockObject
     */
    private function createProfileManagerWithoutProfile(): object
    {
        $profileManager = $this->createMock(ProfileManagerInterface::class);
        $profileManager->method('findByUserId')
            ->with(self::USER_ID)
            ->willReturn(null);

        return $profileManager;
    }

    /**
     * @return ProfileManagerInterface|MockObject
     */
    private function createProfileManagerWithProfile(): object
    {
        $profileManager = $this->createMock(ProfileManagerInterface::class);
        $profileManager->method('findByUserId')
            ->with(self::USER_ID)
            ->willReturn($this->createMock(Profile::class));

        return $profileManager;
    }

    private function createEvent(Request $request): FilterUserResponseEvent
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(self::USER_ID);

        $event = $this->createMock(FilterUserResponseEvent::class);
        $event->method('getRequest')->willReturn($request);
        $event->method('getUser')->willReturn($user);

        return $event;
    }

    /**
     * @param null|string $referralCode
     * @return MockObject|Request
     */
    private function createRequestCodeInCookies(
        ?string $referralCode = self::REFERRAL_CODE
    ): object {
        $cookiesBag = $this->createMock(ParameterBag::class);
        $cookiesBag->method('get')
            ->willReturn($referralCode);

        $request = $this->createMock(Request::class);
        $request->cookies = $cookiesBag;

        return $request;
    }

    /**
     * @param null|string $referralCode
     * @return MockObject|Request
     */
    private function createRequestCodeInSession(
        ?string $referralCode = self::REFERRAL_CODE
    ): object {
        $session = $this->createMock(Session::class);
        $session->method('get')
            ->willReturn($referralCode);

        $request = $this->createRequestCodeInCookies(null);
        $request->method('getSession')->willReturn($session);

        return $request;
    }

    /**
     * @return MockObject|Request
     */
    private function createRequestNoCode(): object
    {
        return $this->createRequestCodeInSession(null);
    }
}
