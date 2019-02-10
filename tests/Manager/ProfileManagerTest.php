<?php

namespace App\Tests\Manager;

use App\Entity\User;
use App\Manager\CryptoManagerInterface;
use App\Manager\ProfileManager;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class ProfileManagerTest extends TestCase
{

    public function testCreateProfileClearsCache(): void
    {
        $userId = 3;
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('clearUserCountCache');
        $userRepository->method('find')
            ->willReturn($this->createMock(User::class));
        $ormAdapter = $this->createMock(OrmAdapterInterface::class);
        $ormAdapter->method('getRepository')
            ->willReturn($userRepository);
        (new ProfileManager($ormAdapter, $this->createMock(CryptoManagerInterface::class)))->createProfile($userId);
    }
}
