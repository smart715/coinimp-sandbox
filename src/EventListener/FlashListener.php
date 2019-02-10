<?php

namespace App\EventListener;

use FOS\UserBundle\EventListener\FlashListener as BaseFlashListener;
use FOS\UserBundle\FOSUserEvents;

class FlashListener extends BaseFlashListener
{
    /** {@inheritdoc} */
    public static function getSubscribedEvents()
    {
        $subscribedEvents = parent::getSubscribedEvents();
        unset($subscribedEvents[FOSUserEvents::REGISTRATION_COMPLETED]);
        return $subscribedEvents;
    }
}
