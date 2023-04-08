<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\Entity\Enum\CemeteryWebPageEnum;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @implements DataTransformerInterface<mixed, string>
 */
class ChoiceToValueTransformer implements DataTransformerInterface
{
    private array $choiceList;

    public function __construct()
    {
        $this->choiceList = CemeteryWebPageEnum::cases();
    }

    public function transform(mixed $choice): mixed
    {
        if (is_array($choice) || empty($choice)) return null;
        return $choice;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (null !== $value && !\is_string($value)) {
            throw new TransformationFailedException('Expected a string or null.');
        }

        $choices = $this->choiceList->getChoicesForValues([(string) $value]);

        if (1 !== \count($choices)) {
            if (null === $value || '' === $value) {
                return null;
            }

            throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique.', $value));
        }

        return current($choices);
    }
}
