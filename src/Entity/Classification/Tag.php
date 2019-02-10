<?php

namespace App\Entity\Classification;

use Sonata\ClassificationBundle\Entity\BaseTag as BaseTag;

class Tag extends BaseTag
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
