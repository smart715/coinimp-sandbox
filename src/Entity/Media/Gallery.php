<?php

namespace App\Entity\Media;

use Sonata\MediaBundle\Entity\BaseGallery as BaseGallery;

class Gallery extends BaseGallery
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
}
