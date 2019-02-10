<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UserEmail extends Constraint
{
    /** @var string */
    public $message = "Email already in use";
}
