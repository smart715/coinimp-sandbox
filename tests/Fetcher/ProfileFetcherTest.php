<?php

namespace App\Tests\Fetcher;

use App\Entity\Profile;
use App\Entity\User;
use App\Fetcher\ProfileFetcher;
use App\Manager\ProfileManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProfileFetcherTest extends TestCase
{
    public function testFetchProfile(): void
    {
        $userId = 2;

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($userId);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $profile = $this->createMock(Profile::class);

        $profileManager = $this->createMock(ProfileManagerInterface::class);
        $profileManager->method('getProfile')->with($userId)->willReturn($profile);

        $fetcher = new ProfileFetcher($profileManager, $tokenStorage);
        $this->assertEquals($profile, $fetcher->fetchProfile());
    }

    public function testFetchProfileThrowsExceptionIfTokenStorageDoesNotContainToken(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $profileManager = $this->createMock(ProfileManagerInterface::class);
        $this->expectException(RuntimeException::class);
        (new ProfileFetcher($profileManager, $tokenStorage))->fetchProfile();
    }
}
