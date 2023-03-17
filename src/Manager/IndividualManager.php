<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\Marriage;
use App\Repository\IndividualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class IndividualManager extends GenealogySecurityManager
{
    /**
     * @var Individual|null
     */
    var ?Individual $individual;

    /**
     * @var IndividualRepository
     */
    var IndividualRepository $repository;

    /**
     * @var EntityManagerInterface
     */
    var EntityManagerInterface $entityManager;

    /**
     * @var Collection
     */
    var Collection $children;

    /**
     * @var Collection
     */
    var Collection $siblings;

    /**
     * @var IndividualNameManager
     */
    var IndividualNameManager $nameManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->setRepository($this->getEntityManager()->getRepository(Individual::class));
        $this->children = new ArrayCollection();
        $this->siblings = new ArrayCollection();
        parent::__construct();
    }

    /**
     * @return Individual|null
     */
    public function getIndividual(): ?Individual
    {
        return $this->individual;
    }

    /**
     * @param Individual|null $individual
     * @return $this
     */
    public function setIndividual(?Individual $individual): IndividualManager
    {
        $this->individual = $individual;
        return $this;
    }

    /**
     * @param string $individualID
     * @return $this
     */
    public function retrieveIndividual(string $individualID): IndividualManager
    {
        $this->individual = $this->getRepository()->findOneByUserID($individualID);
        if ($this->getIndividual() === null) return $this;
        if (!is_null($this->individual->getFather())) $this->individual->getFather()->getFirstName();
        if (!is_null($this->individual->getMother())) $this->individual->getMother()->getFirstName();

        $this->getChildren();
        $this->getSiblings();
        return $this;
    }

    /**
     * @return IndividualRepository
     */
    public function getRepository(): IndividualRepository
    {
        return $this->repository;
    }

    /**
     * @param IndividualRepository $repository
     * @return IndividualManager
     */
    public function setRepository(IndividualRepository $repository): IndividualManager
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param Individual|null $individual
     * @return string
     */
    public function getGenealogyFullName(?Individual $individual = null): string
    {
        $individual = is_null($individual) ? $this->getIndividual() : $individual;

        return $this->getNameManager()->getFullNameWithDates($individual);
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getChildren(): Collection
    {
        // check if father
        $this->children = new ArrayCollection($this->getRepository()->findBy(['father' => $this->getIndividual()]));
        // check if mother
        foreach ($this->getRepository()->findBy(['mother' => $this->getIndividual()]) as $child) {
            if (!$this->children->contains($child)) $this->children->add($child);
        }
        // sort
        // Collect an array iterator.
        $iterator = $this->children->getIterator();

        // Do sort the new iterator.
        $iterator->uasort(function (Individual $a, Individual $b) {
            return ($a->getBirthDateFirstNameString() > $b->getBirthDateFirstNameString()) ? -1 : 1;
        });
        $this->children = new ArrayCollection(iterator_to_array($iterator));
        return $this->children;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getSiblings(): Collection
    {
        // check if father
        if (is_null($this->getIndividual()->getFather())) {
            $this->siblings = new ArrayCollection();
        } else {
            $this->siblings = new ArrayCollection($this->getRepository()->findBy(['father' => $this->getIndividual()->getFather()]));
        }
        // check if mother
        if (!is_null($this->getIndividual()->getMother())) {
            foreach ($this->getRepository()->findBy(['mother' => $this->getIndividual()->getMother()]) as $child) {
                if (!$this->siblings->contains($child)) $this->siblings->add($child);
            }
        }
        // sort
        // Collect an array iterator.
        $iterator = $this->siblings->getIterator();

        // Do sort the new iterator.
        $iterator->uasort(function (Individual $a, Individual $b) {
            return ($a->getBirthDateFirstNameString() > $b->getBirthDateFirstNameString()) ? -1 : 1;
        });
        $this->siblings = new ArrayCollection(iterator_to_array($iterator));
        if ($this->siblings->contains($this->getIndividual())) $this->siblings->removeElement($this->getIndividual());

        return $this->siblings;
    }

    /**
     * @return array
     */
    public function getBirthDetails(): array
    {
        $access = $this->isFamilyTreePublic($this->getIndividual());
        $result['status'] = 'in';
        $result['date'] = '';
        $result['location'] = '';

        if ($access) {
            $result['date'] = $this->getIndividual()->parseEventDate($this->getIndividual()->getBirthDate());
            if (preg_match('#^([0-9]{1,2}) ([a-zA-Z]{3}) ([0-9]{4})$#',$result['date'], $matches)) {
                $result['date'] = $this->getIndividual()->getBirthDate()->format('l, jS F Y');
                $result['status'] = 'on_the';
            }
            $result['location'] = $this->getIndividual()->getBirthLocation();
        } else {
            $result['date'] = $this->getIndividual()->parseEventDate($this->getIndividual()->getBirthDate(), true);
            $result['status'] = 'decade_only';
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getDeathDetails(): array
    {
        $access = $this->isFamilyTreePublic($this->getIndividual());
        $result['status'] = 'in';
        $result['location_status'] = 'empty';
        $result['date'] = '';
        $result['location'] = '';

        if ($access) {
            $result['date'] = $this->getIndividual()->parseEventDate($this->getIndividual()->getDeathDate());
            if (preg_match('#^([0-9]{1,2}) ([a-zA-Z]{3}) ([0-9]{4})$#',$result['date'], $matches)) {
                $result['date'] = $this->getIndividual()->getDeathDate()->format('l, jS F Y');
                $result['status'] = 'on_the';
            }
            $result['location'] = $this->getIndividual()->getDeathLocation();
            if (!empty($result['location'])) $result['location_status'] = 'ok';

        } else {
            $result['date'] = $this->getIndividual()->parseEventDate($this->getIndividual()->getDeathDate(), true);
            $result['status'] = 'decade_only';
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getParentDetails(): array
    {
        $result = [];
        if (is_null($this->getIndividual()->getFather())) {
            $result['father'] = 'birth.unknown.father';
        } else {
            $result['father'] = $this->getGenealogyFullName($this->getIndividual()->getFather());
        }
        if (is_null($this->getIndividual()->getMother())) {
            $result['mother'] = 'birth.unknown.mother';
        } else {
            $result['mother'] = $this->getGenealogyFullName($this->getIndividual()->getMother());
        }
        $result['gender'] = $this->getIndividual()->getGender();
        $result['m_id'] = $this->getIndividual()->getMother() ? $this->getIndividual()->getMother()->getUserID() : null;
        $result['f_id'] = $this->getIndividual()->getFather() ? $this->getIndividual()->getFather()->getUserID() : null;
        return $result;
    }

    /**
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getMarriages(?Individual $individual = null): ArrayCollection
    {
        if ($individual === null) $individual = $this->getIndividual();

        if ($individual->getMarriages()->count() > 0) return $individual->getMarriages();

        $repo = $this->getEntityManager()->getRepository(Marriage::class);

        $marriages = new ArrayCollection($repo->findBySpouse($individual));

        $iterator = $marriages->getIterator();
        $iterator->uasort(function (Marriage $a, Marriage $b) {
            return ($a->getMarriageDate() > $b->getMarriageDate()) ? -1 : 1;
        });
        $individual->setMarriages(new ArrayCollection(iterator_to_array($iterator)));

        return $individual->getMarriages();
    }

    /**
     * @param Marriage $spouse
     * @return array
     */
    public function getMarriageDetails(Marriage $spouse): array
    {
        $result = [];
        $result['date_status'] = $spouse->getMarriageDateStatus();
        $result['date'] = $this->getIndividual()->parseEventDate($spouse->getMarriageDate());
        if (strlen($result['date']) > 8) {
            $result['date'] = $spouse->getMarriageDate()->format('l, jS F Y');
            $result['date_status'] = ($result['date_status'] === 'certain' || $result['date_status'] === null) ? 'on' : $result['date_status'];
        } elseif (empty($result['date'])) {
            $result['date_status'] = 'empty';
        }
        $result['name'] = $this->getIndividual() === $spouse->getHusband() ? $this->getGenealogyFullName($spouse->getWife()) : $this->getGenealogyFullName($spouse->getHusband());
        $result['location'] = $spouse->getLocation();
        $result['location_status'] = $spouse->getLocationStatus();
        $result['gender'] = $this->getIndividual()->getGender();
        $result['spouse_id'] = $this->getIndividual() === $spouse->getHusband() ? $spouse->getWife()->getUserID() : $spouse->getHusband()->getUserID();
        if ($result['date_status'] === null) {
            //do stuff
        }
        if ($result['location_status'] === null) {
            if (empty($result['location']))
            {
                $result['location_status'] = 'empty';
            } else {
                $result['location_status'] = 'certain';
            }
        }

        return $result;
    }

    /**
     * @return IndividualNameManager
     */
    public function getNameManager(): IndividualNameManager
    {
        return $this->nameManager = $this->nameManager ?? new IndividualNameManager();
    }
}