<?php
namespace App\Form\Validation;

use App\Entity\Location;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocationConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof LocationConstraint) {
            throw new UnexpectedTypeException($constraint, LocationConstraint::class);
        }

        if (empty($value)) {
            $this->context->buildViolation('A location category is required.')
                ->addViolation();
            return;
        }

        if ($value instanceof Location) return;

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value->getDisplayName())
            ->addViolation();
    }
}