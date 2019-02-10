<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Manager\UserManagerInterface;
use App\OrmAdapter\OrmAdapterInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfirmationUserSubscriber implements EventSubscriberInterface
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var OrmAdapterInterface */
    private $ormAdapter;

    public function __construct(UserManagerInterface $userManager, OrmAdapterInterface $ormAdapter)
    {
        $this->userManager = $userManager;
        $this->ormAdapter = $ormAdapter;
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): array
    {
        return [
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onConfirmed',
        ];
    }

    public function onConfirmed(FilterUserResponseEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        if (!empty($user->getTempEmail()))
            $this->updateUser($user);
    }

    private function updateUser(User $user): void
    {
        $this->userManager->confirmTempEmail($user);
        $this->ormAdapter->persist($user);
        $this->ormAdapter->flush();
    }
}
