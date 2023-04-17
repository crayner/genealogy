<?php

namespace App\Form\Validation;

use App\Entity\Category;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WebPagesConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$value instanceof Collection) return;
        foreach ($value as $q=>$webpage) {
            $category = $webpage->getCategory();
            if ($webpage->getDefinedType()->isNotUsed()) {
                if (empty($webpage->getName() || empty($webpage->getPrompt()) || empty($webpage->getUrl()))) {
                    $this->context->buildViolation('The name, prompt and url are required for a self defined ({{ string }}) web page.')
                        ->setParameter('{{ string }}', $webpage->getName())
                        ->addViolation();
                }
            } else {
                $definition = $webpage->getDefinedType()->getDefinition();
                if (preg_match($definition['test'], $webpage->getKey()) !== 1) {
                    $this->context->buildViolation('The key ({{ key }}) for {{ definedType }} is not valid.')
                        ->setParameter('{{ definedType }}', $definition['name'])
                        ->setParameter('{{ key }}', $webpage->getKey())
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param Category $category
     * @return array
     */
    private function getWebPageDefinitions(Category $category): array
    {
        $d = '\App\Entity\Enum\\' . basename(get_class($category)) . "WebPageEnum";
        return $d::cases();
    }
}