<?php

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class NameSearch
{
    /**
     * @var string|null
     */
    var ?string $familyName;

    /**
     * @var string|null
     */
    var ?string $givenNames;

    /**
     * @var string|null
     */
    var Collection $list;

    /**
     * NameSearch Constructor
     */
    public function __construct()
    {
        $this->list = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFamilyName(): string
    {
        return $this->familyName = $this->familyName ?? '';
    }

    /**
     * @param string|null $familyName
     * @return NameSearch
     */
    public function setFamilyName(?string $familyName): NameSearch
    {
        $this->familyName = $familyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getGivenNames(): string
    {
        return $this->givenNames = $this->givenNames ?? '';
    }

    /**
     * @param string|null $givenNames
     * @return NameSearch
     */
    public function setGivenNames(?string $givenNames): NameSearch
    {
        $this->givenNames = $givenNames;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @param Collection|null $list
     * @return NameSearch
     */
    public function setList(?Collection $list): NameSearch
    {
        $this->list = $list ?? new ArrayCollection();
        return $this;
    }
}