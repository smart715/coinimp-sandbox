<?php

namespace App\Entity\News;

use Sonata\NewsBundle\Entity\BaseComment as BaseComment;

class Comment extends BaseComment
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
