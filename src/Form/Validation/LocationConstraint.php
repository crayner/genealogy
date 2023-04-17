<?php

namespace App\Form\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class LocationConstraint extends Constraint
{
    public string $message = 'This category, {{string}}, requires a valid location category.';

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}