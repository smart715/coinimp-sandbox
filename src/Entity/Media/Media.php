<?php

namespace App\Entity\Media;

use Sonata\MediaBundle\Entity\BaseMedia as BaseMedia;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

class Media extends BaseMedia
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId() :?int
    {
        return $this->id;
    }

    public function getProviderStatus()
    {
        if(!$this->providerStatus)
            $this->setProviderStatus($this::STATUS_ERROR);

        return $this->providerStatus;
    }

    public function getProviderReference()
    {
        if(!$this->providerReference)
            $this->setProviderReference(ProviderNotFoundException::class);

        return $this->providerReference;

    }

}
