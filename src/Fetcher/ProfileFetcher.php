<?php

namespace App\Fetcher;

use App\Entity\Profile;
use App\Manager\ProfileManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileFetcher implements ProfileFetcherInterface
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        ProfileManagerInterface $profileManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->profileManager = $profileManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function fetchProfile(): Profile
    {
        if (null === $this->tokenStorage->getToken())
            throw new RuntimeException('Not authenticated.');

        /** @var UserInterface */
        $user = $this->tokenStorage->getToken()->getUser();
        return $this->profileManager->getProfile($user->getId());
    }
}
