<?php

namespace App\Entity;

use App\Repository\ParseParentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParseParentRepository::class)]
#[ORM\Table(name: 'parse_parent', options: ['collate' => 'utf8mb4_unicode_ci'])]
class ParseParent
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    private int $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    private int $child;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    private int $parent;

    /**
     * @var string
     */
    #[ORM\Column(type: 'enum', length: 32)]
    private string $relationship;

    /**
     * @var array|string[]
     */
    static private array $relationshipList = [
        'Mother',
        'Father',
        'Manager'
    ];

    public function __construct(?int $child = null, ?int $parent = null, ?string $relationship = null)
    {
        $this->child = $child;
        $this->parent = $parent;
        $this->relationship = $relationship;
    }
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ParseParent
     */
    public function setId(int $id): ParseParent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getChild(): int
    {
        return $this->child;
    }

    /**
     * @param int $child
     * @return ParseParent
     */
    public function setChild(int $child): ParseParent
    {
        $this->child = $child;
        return $this;
    }

    /**
     * @return int
     */
    public function getParent(): int
    {
        return $this->parent;
    }

    /**
     * @param int $parent
     * @return ParseParent
     */
    public function setParent(int $parent): ParseParent
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelationship(): string
    {
        return $this->relationship;
    }

    /**
     * @param string $relationship
     * @return ParseParent
     */
    public function setRelationship(string $relationship): ParseParent
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelationshipList(): array
    {
        return static::$relationshipList;
    }
}