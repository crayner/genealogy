<?php

namespace App\Form\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class WebPagesConstraint extends Constraint
{
    public function validatedBy()
    {
        return static::class.'Validator';
    }

}