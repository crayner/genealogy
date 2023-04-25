<?php

namespace App\Manager;

use App\Entity\Individual;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndividualNameManager extends GenealogySecurityManager
{

    /**
     * @param Individual|null $individual
     * @return string
     */
    public function getFullNameWithDates(?Individual $individual): string
    {
        return $this->getFullName($individual, ['dates' => true]);
    }

    /**
     * @param Individual|null $individual
     * @param array $options
     * @return string
     */
    public function getFullName(?Individual $individual, array $options = []): string
    {
        if (is_null($individual)) return '';

        $options = $this->resolveNameOptions($options);

        $name = '';

        $access = $this->isFamilyTreePublic($individual);

        if ($access) $name .= ' ' . $individual->getPrefix();
        $name .= ' ' . $individual->getFirstName();

        if (!empty($individual->getMiddleName())) {
            if ($access) {
                    $name .= ' ' . $individual->getMiddleName();
            } else {
                    $name .= ' ' . substr($individual->getMiddleName(), 0, 1) . '.';
            }
        }

        if ($access && $individual->getNickNames() !== null) {
            $name .= ' "' . $individual->getNickNames() .'"';
        }

        if ($access && !$options['words'] && $individual->getLastNameCurrent() !== $individual->getLastNameAtBirth()) {
            $name .= ' (' . $individual->getLastNameAtBirth() . ')';
        }

        $name .= ' ' . $individual->getLastNameCurrent();

        if ($access && $options['words'] && $individual->getLastNameCurrent() !== $individual->getLastNameAtBirth()) {
            $name .= ' formerly ' . $individual->getLastNameAtBirth();
        }
        if ($access && $individual->getLastNameOther() !== null) {
            $name .= ' aka ' . $individual->getLastNameOther();
        }
        if ($access) $name .= ' ' . $individual->getSuffix();

        if ($options['dates']) {
            if ($access && $individual->getBirthDate() instanceof \DateTimeImmutable) {
                $name .= ' (' . $individual->getBirthDate()->format('Y');
            } elseif ($access && !$individual->getBirthDate() instanceof \DateTimeImmutable) {
                $name .= ' ( ?';
            }
            if ($access && $individual->getDeathDate() instanceof \DateTimeImmutable) {
                $name .= ' - ' . $individual->getDeathDate()->format('Y') . ')';
            } elseif ($access && !$individual->getDeathDate() instanceof \DateTimeImmutable && !$individual->isLiving()) {
                $name .= ' - ? )';
            } elseif ($access && !$individual->getDeathDate() instanceof \DateTimeImmutable && $individual->isLiving()) {
                $name .= ' - )';
            }
        }
        return trim($name);
    }

    /**
     * @param Individual|null $individual
     * @return string
     */
    public function getShortName(Individual $individual): string
    {
        if (is_null($individual)) return '';

        $name = '';

        $access = $this->isFamilyTreePublic($individual);
        if ($access) {
            $name .= ' ' . $individual->getFirstName();
        } else {
            $name .= ' ' . substr($individual->getFirstName(), 0, 1);
        }

        if ($individual->getLastNameAtBirth() === $individual->getLastNameCurrent()) {
            $name .= ' ' . $individual->getLastNameAtBirth();
        } else {
            if ($access) {
                $name .= ' (' . $individual->getLastNameAtBirth() . ') ' . $individual->getLastNameCurrent();
            } else {
                $name .= $individual->getLastNameAtBirth();
            }
        }

        return trim($name);
    }

    public function getShortEventDate(?Individual $individual, string $event): string
    {
        if (is_null($individual)) return '';

        $result = '';

        $access = $this->isFamilyTreePublic($individual);

        switch ($event) {
            case 'birth':
                if ($access && $individual->getBirthDate() instanceof \DateTimeImmutable) {
                    if ($individual->getBirthDate()->format('s') === '01') {
                        $result .= $individual->getBirthDate()->format('j M Y');
                    } elseif ($individual->getBirthDate()->format('i') === '01') {
                        $result .= $individual->getBirthDate()->format('M Y');
                    } else {
                        $result .= $individual->getBirthDate()->format('Y');
                    }
                } elseif (!$access && $individual->getBirthDate() instanceof \DateTimeImmutable) {
                    $result .= $individual->getBirthDate()->format('Y\s');
                }
                break;
            case 'death':
                if ($access && $individual->getdeathDate() instanceof \DateTimeImmutable) {
                    if ($individual->getdeathDate()->format('s') === '01') {
                        $result .= $individual->getdeathDate()->format('j M Y');
                    } elseif ($individual->getdeathDate()->format('i') === '01') {
                        $result .= $individual->getdeathDate()->format('M Y');
                    } else {
                        $result .= $individual->getdeathDate()->format('Y');
                    }
                } elseif (!$access && $individual->getdeathDate() instanceof \DateTimeImmutable) {
                    $result .= $individual->getdeathDate()->format('Y\s');
                }
                break;
            default:
                dd($event);
        }

        return $result;
    }

    /**
     * @param array $options
     * @return array
     */
    public function resolveNameOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'dates' => true,
            'words' => true,
            'birth_only' => false,
        ]);
        return $resolver->resolve($options);
    }


    /**
     * @param Individual|null $individual
     * @param array $options
     * @return array
     */
    public function getFullNameDetails(?Individual $individual, array $options = []): array
    {
        $result = [];
        if (is_null($individual)) return ['current' => 'same', 'aka' => 'empty', 'middle' => 'empty'];
        $access = $this->isFamilyTreePublic($individual);
        $options = $this->resolveNameOptions($options);

        $result['first_name'] = $individual->getFirstName();
        $result['prefix'] = '';
        if ($access && !empty($individual->getPrefix())) $result['prefix'] = $individual->getPrefix() . ' ';

        $result['middle'] = 'empty';
        if (!empty($individual->getMiddleName())) {
            if ($access) {
                $result['middle_name'] = $individual->getMiddleName();
                $result['middle'] = 'used';
            } else {
                $result['middle_name'] = substr($individual->getMiddleName(), 0, 1) . '.';
                $result['middle'] = 'used';
            }
        }
        $result['nick_names'] = '';
        if ($access && !empty($individual->getNickNames())) {
            $result['nick_names'] = ' "' . $individual->getNickNames() . '"';
        }

        $result['last_name_current'] = $individual->getLastNameCurrent();
        $result['current'] = 'same';

        if ($access && $individual->getLastNameCurrent() !== $individual->getLastNameAtBirth()) {
            $result['last_name_at_birth'] = $individual->getLastNameAtBirth();
            $result['current'] = 'changed';
        } else {
            $result['last_name_at_birth'] = $individual->getLastNameCurrent();
        }

        $result['aka'] = 'empty';
        if ($access && $individual->getLastNameOther() !== null) {
            $result['last_name_other'] = $individual->getLastNameOther();
            $result['aka'] = 'used';
        }
        $result['access'] = $access;

        if ($options['dates']) {
            if ($access && $individual->getBirthDate() instanceof \DateTimeImmutable) {
                $result['birth_year'] = $individual->getBirthDate()->format('Y');
            } else {
                $result['birth_year'] = '?';
            }
            if ($access && $individual->getDeathDate() instanceof \DateTimeImmutable) {
                $result['death_year'] = $individual->getDeathDate()->format('Y');
            } else {
                $result['death_year'] = '?';
            }
        }
        $result['suffix'] = '';
        if ($access && !empty($individual->getSuffix())) $result['suffix'] = ' ' .$individual->getSuffix();

        return $result;
    }

}