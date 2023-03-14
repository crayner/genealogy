<?php
namespace App\Manager;

use App\Entity\Individual;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Security From Wikitree has been converted into a binary number, as there system was limited.
 * So:
 *    Wikitree     Here
 *    0 - 20        0      Private
 *                  1      Invalid
 *        30        2      Private with Public Biography
 *                  3      Invalid
 *        35        4      Private with Public Family Tree
 *                  5      Invalid
 *        40        6      Private with Public Biography and Family Tree
 *        50        14     Public (opens Comments for view.)
 *        60        15      Open
 */
class GenealogySecurityManager
{
    /**
     * @var ArrayCollection 
     */
    var ArrayCollection $publicTreeAccess;

    /**
     * @param ArrayCollection $publicTreeAccess
     */
    public function __construct()
    {
        $this->publicTreeAccess = new ArrayCollection();
    }

    /**
     * @param Individual $individual
     * @return bool
     */
    public function isFamilyTreePublic(Individual $individual): bool
    {
        if ($this->hasPublicTreeAccess($individual)) return $this->getPublicTreeAccess($individual);
        
        if ($individual->getPrivacy() || 4) return $this->setPublicTreeAccess($individual, true);

        if ($individual->getBirthDate() instanceof \DateTimeImmutable && $individual->getBirthDate()->format('Y') <= date('Y', '-150 Years')) {
            return $this->setPublicTreeAccess($individual, true);
        }

        if ($individual->getDeathDate() instanceof \DateTimeImmutable && $individual->getDeathDate()->format('Y') <= date('Y', '-100 Years')) {
            return $this->setPublicTreeAccess($individual, true);
        }

        return $this->setPublicTreeAccess($individual, false);
    }

    /**
     * @param Individual $individual
     * @return bool
     */
    public function hasPublicTreeAccess(Individual $individual): bool
    {
        return $this->publicTreeAccess->containsKey($individual->getId());
    }

    /**
     * @param Individual $individual
     * @return bool
     */
    public function getPublicTreeAccess(Individual $individual): bool
    {
        return $this->publicTreeAccess->get($individual->getId());
    }

    /**
     * @param Individual $individual
     * @param bool $value
     * @return bool
     */
    public function setPublicTreeAccess(Individual $individual, bool $value): bool
    {
        $this->publicTreeAccess->set($individual->getId(), $value);
        return $this->publicTreeAccess->get($individual->getId());
    }
}