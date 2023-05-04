<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\IndividualIndex;
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
     * @param int|string $individualID
     * @return $this
     * @throws \Exception
     */
    public function retrieveIndividual(int|string $individualID): IndividualManager
    {
        $this->individual = $this->getRepository()->find($individualID);
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
    public function getFullName(?Individual $individual = null, array $options = []): string
    {
        $individual = is_null($individual) ? $this->getIndividual() : $individual;

        return $this->getNameManager()->getFullName($individual, $options);
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
            return ($a->getBirthDateFirstNameString() > $b->getBirthDateFirstNameString()) ? 1 : -1;
        });
        $this->children = new ArrayCollection(array_values(iterator_to_array($iterator)));
        return $this->children;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getSiblings(): Collection
    {
        $this->siblings = new ArrayCollection();
        // check if father
        if ($this->getIndividual()->getFather() instanceof Individual) {
            foreach ($this->getRepository()->findBy(['father' => $this->getIndividual()->getFather()]) as $child) {
                if (!$this->siblings->contains($child)) $this->siblings->add($child);
            }
        }
        // check if mother
        if ($this->getIndividual()->getMother() instanceof Individual) {
            foreach ($this->getRepository()->findBy(['mother' => $this->getIndividual()->getMother()]) as $child) {
                if (!$this->siblings->contains($child)) $this->siblings->add($child);
            }
        }
        // sort
        // Collect an array iterator.
        $iterator = $this->siblings->getIterator();

        // Do the sort.
        $iterator->uasort(function (Individual $a, Individual $b) {
            return ($a->getBirthDateFirstNameString() < $b->getBirthDateFirstNameString()) ? -1 : 1;
        });
        $this->siblings = new ArrayCollection(iterator_to_array($iterator));
        if ($this->siblings->contains($this->getIndividual())) $this->siblings->removeElement($this->getIndividual());

        return $this->siblings = new ArrayCollection(array_values($this->siblings->toArray()));;
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
                $result['status'] = 'on';
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
                $result['status'] = 'on';
            }

            $result['location'] = $this->getIndividual()->getDeathLocation();

            if (!empty($result['location'])) {
                $result['location_status'] = 'ok';
            }

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
     * @param Marriage $marriage
     * @return array
     */
    public function getMarriageDetails(Marriage $marriage): array
    {
        $result = [];
        $result['date_status'] = $marriage->getMarriageDateStatus();
        $result['date'] = $this->getIndividual()->parseEventDate($marriage->getMarriageDate());
        if (strlen($result['date']) > 8) {
            $result['date'] = $marriage->getMarriageDate()->format('l, jS F Y');
            $result['date_status'] = ($result['date_status'] === 'certain' || $result['date_status'] === null) ? 'on' : $result['date_status'];
        } elseif (empty($result['date'])) {
            $result['date_status'] = 'empty';
        }
        $result['name'] = $this->getFullName($marriage->getSpouse($this->getIndividual()), ['dates' => true, 'words' => false]);
        $result['location'] = $marriage->getLocation();
        $result['location_status'] = $marriage->getLocationStatus();
        $result['gender'] = $this->getIndividual()->getGenderValue();
        $result['spouse_id'] = $marriage->getSpouse($this->getIndividual())->getId();
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

    /**
     * @return string[]
     */
    public function getIndividualDetails(): array
    {
        $result = [
            'full_name' => $this->getNameManager()->getFullNameDetails($this->getIndividual(), ['dates' => true]),
            'birth_details' => $this->getBirthDetails(),
            'death_details' => $this->getDeathDetails(),
            'parents' => [
                'father' => $this->getIndividual()->getFather() ? $this->getIndividual()->getFather()->getId() : 0,
                'mother' => $this->getIndividual()->getMother() ? $this->getIndividual()->getMother()->getId() : 0,
            ],
            'siblings' => [],
            'spouses' => [],
            'gender' => $this->getIndividual()->getGenderValue(),
        ];
        $result['siblings'] = [];
        foreach ($this->getSiblings() as $q=>$sibling) {
            $result['siblings'][$q] = $sibling->getId();
        }
        $result['spouses'] = [];
        foreach ($this->getMarriages() as $q=>$marriage) {
            $result['spouses'][$q]['id'] = $marriage->getId();
            $result['spouses'][$q]['details'] = $this->getMarriageDetails($marriage);
        }
        $result['children'] = [];
        foreach ($this->getChildren() as $q=>$child) {
            $result['children'][$q]['id'] = $child->getId();
            $result['children'][$q]['name'] = $this->getFullName($child, ['words' => false]);
        }
        return $result;
    }
}