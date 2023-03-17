<?php

namespace App\Manager;

use App\Entity\Individual;

class IndividualNameManager extends GenealogySecurityManager
{
    /**
     * @param Individual|null $individual
     * @return string
     */
    public function getFullNameWithDates(?Individual $individual): string
    {
        if (is_null($individual)) return '';

        $name = '';

        $access = $this->isFamilyTreePublic($individual);

        if ($access) $name .= ' ' . $individual->getPrefix();
        if ($access) $name .= ' ' . $individual->getFirstName();

        if ($access) {
            if (!empty($individual->getMiddleName())) $name .= ' ' . $individual->getMiddleName();
        } else {
            if (!empty($individual->getMiddleName())) $name .= ' ' . substr($individual->getMiddleName(), 0, 1) . '.';
        }

        if ($access && $individual->getNickNames() !== null) {
            $name .= ' "' . $individual->getNickNames() .'"';
        }

        $name .= ' ' . $individual->getLastNameCurrent();

        if ($access && $individual->getLastNameCurrent() !== $individual->getLastNameAtBirth()) {
            $name .= ' formerly ' . $individual->getLastNameAtBirth();
        }
        if ($access && $individual->getLastNameOther() !== null) {
            $name .= ' aka ' . $individual->getLastNameOther();
        }

        if ($access && $individual->getBirthDate() instanceof \DateTimeImmutable) {
            $name .= ' (' . $individual->getBirthDate()->format('Y');
        } elseif ($access && !$individual->getBirthDate() instanceof \DateTimeImmutable) {
            $name .= ' ( ?';
        }
        if ($access && $individual->getDeathDate() instanceof \DateTimeImmutable) {
            $name .= ' - ' . $individual->getDeathDate()->format('Y') . ')';
        } elseif ($access && !$individual->getDeathDate() instanceof \DateTimeImmutable) {
            $name .= ' - ? )';
        }
        if ($access) $name .= ' ' . $individual->getSuffix();
        return trim($name);
    }

    /**
     * @param Individual|null $individual
     * @return string
     */
    public function getShortName(?Individual $individual): string
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
}