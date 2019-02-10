<?php

namespace App\Entity\Classification;

use Sonata\ClassificationBundle\Entity\BaseCollection as BaseCollection;

class Collection extends BaseCollection
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
