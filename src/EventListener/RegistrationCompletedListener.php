<?php

namespace App\EventListener;

use App\Manager\ProfileManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 * Listener of the event "visitor submitted a valid registration form and his User is
 * saved". The purpose is to create supplementary Profile entity which will hold our
 * application-specific data, without data needed for authentication which is held by
 * User entity.
 */
class RegistrationCompletedListener
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var FilterUserResponseEvent|null */
    private $event;

    public function __construct(ProfileManagerInterface $profileManager)
    {
        $this->profileManager = $profileManager;
    }

    public function onFosuserRegistrationCompleted(FilterUserResponseEvent $event): void
    {
        $this->event = $event;
        $this->createProfile();
        $this->event = null;
    }

    private function createProfile(): void
    {
        $userId = $this->event->getUser()->getId();

        if (!is_null($this->profileManager->findByUserId($userId)))
            return;
        elseif (!is_null($this->extractReferralCode()))
            $this->profileManager->createProfileReferral(
                $userId,
                $this->extractReferralCode()
            );
        elseif (!is_null($this->extractReferralCode('ico_referral')))
            $this->profileManager->createProfileReferral(
                $userId,
                $this->extractReferralCode('ico_referral'),
                true
            );
        else $this->profileManager->createProfile($userId);
    }

    private function extractReferralCode(string $referral = 'referral'): ?string
    {
        return $this->event->getRequest()->cookies->get($referral)
            ?? $this->event->getRequest()->getSession()->get($referral)
            ?? null;
    }
}
