<?php

namespace App\Entity;

use App\Exception\FamilyException;
use App\Exception\RelationshipException;
use App\Repository\IndividualFamilyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class IndividualFamily
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 10:58
 * @ORM\Entity(repositoryClass=IndividualFamilyRepository::class)
 * @ORM\Table(name="individual_family", uniqueConstraints={@ORM\UniqueConstraint(name="individual_family",columns={"individual","family"})})
 * @UniqueEntity({"individual","family"})
 */
class IndividualFamily
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity=Individual::class, inversedBy="families")
     * @ORM\JoinColumn(nullable=false, name="individual")
     */
    private Individual $individual;

    /**
     * @ORM\ManyToOne(targetEntity=Family::class, inversedBy="individuals")
     * @ORM\JoinColumn(nullable=false, name="family")
     */
    private Family $family;

    /**
     * @ORM\Column(type="enum")
     */
    private string $relationshipType;

    private static array $relationshipTypeList = [
        'Spouse',
        'Child',
        'Husband',
        'Wife',
    ];

    /**
     * IndividualFamily constructor.
     * @param Individual|null $individual
     * @param Family|null $family
     * @param string|null $relationshipType
     */
    public function __construct(?Individual $individual = null, ?Family $family = null, ?string $relationshipType = null)
    {
        if (!is_null($individual)) $this->setIndividual($individual);
        if (!is_null($family)) $this->setFamily($family);
        if (!is_null($relationshipType)) $this->setRelationshipType($relationshipType);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Individual
     */
    public function getIndividual(): Individual
    {
        return $this->individual;
    }

    /**
     * @param Individual $individual
     * @return IndividualFamily
     */
    public function setIndividual(Individual $individual): IndividualFamily
    {
        $this->individual = $individual;
        return $this;
    }

    /**
     * @return Family
     */
    public function getFamily(): Family
    {
        return $this->family;
    }

    /**
     * @param Family $family
     * @return IndividualFamily
     */
    public function setFamily(Family $family): IndividualFamily
    {
        $this->family = $family;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelationshipType(): string
    {
        return $this->relationshipType;
    }

    /**
     * @param string $relationshipType
     * @return IndividualFamily
     */
    public function setRelationshipType(string $relationshipType): IndividualFamily
    {
        if (!in_array($relationshipType, self::getRelationshipTypeList())) throw new RelationshipException($this, sprintf('The relationship type (%s) must be one of [%s].', $relationshipType, implode(', ', self::getRelationshipTypeList())));
        $this->relationshipType = $relationshipType;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getRelationshipTypeList(): array
    {
        return self::$relationshipTypeList;
    }
}
