<?php

namespace App\Entity\Classification;

use Sonata\ClassificationBundle\Entity\BaseCategory as BaseCategory;

class Category extends BaseCategory
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
