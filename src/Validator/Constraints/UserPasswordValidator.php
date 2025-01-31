<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserPasswordValidator extends ConstraintValidator
{
    /** {@inheritdoc} */
    public function validate($value, Constraint $constraint)
    {
        if (!empty($value) && !preg_match('/(?=.*[\p{Lu}])(?=.*[\p{Ll}])(?=.*[\p{N}]).{8,}/', $value))
            $this->context->buildViolation($constraint->message)->addViolation();
    }
}
