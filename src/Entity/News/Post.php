<?php

namespace App\Entity\News;

use Sonata\NewsBundle\Entity\BasePost as BasePost;

class Post extends BasePost
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
