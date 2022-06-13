<?php

namespace App\Entity;

use App\Exception\AttributeException;
use App\Repository\AttributeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class Attribute
 * @selectPure  App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 09:21
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 * @ORM\Table(name="attribute",
 *     indexes={@ORM\Index(name="source",columns={"source"})})
 */
class Attribute
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)    
     */
    private ?string $id;

    /**
     * @var string|null
     * @ORM\Column(length=120, nullable=true)
     */
    private ?string $email;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var string
     * @ORM\Column(type="enum")
     */
    private string $type;

    /**
     * @var array|string[]
     */
    private static array $typeList = [
        'Residence'
    ];

    /**
     * @var string|null
     * @ORM\Column(length=120)
     */
    private ?string $place;

    /**
     * @var SourceData|null
     * @ORM\ManyToOne(targetEntity="App\Entity\SourceData")
     * @ORM\JoinColumn(name="source",nullable=true)
     */
    private ?SourceData $source;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Attribute
     */
    public function setId(?string $id): Attribute
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return isset($this->email) ? $this->email : null;
    }

    /**
     * @param string|null $email
     * @return Attribute
     */
    public function setEmail(?string $email): Attribute
    {
        if (is_string($email) && mb_strlen($email) < 5 && mb_strlen($email) > 120) throw new AttributeException($this,sprintf('The Email address given must be >= 5 and <= 120 characters in length.'));
        $this->email = $email;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return Attribute
     */
    public function setOffset(int $offset): Attribute
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Attribute
     */
    public function setType(string $type): Attribute
    {
        if (!in_array($type, self::getTypeList())) throw new AttributeException($this, sprintf('The attribute type (%s) must be one of [%s].',$type, implode(', ',self::getTypeList())));
        $this->type = $type;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return string|null
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }

    /**
     * @param string|null $place
     * @return Attribute
     */
    public function setPlace(?string $place): Attribute
    {
        $this->place = $place;
        return $this;
    }

    /**
     * @return SourceData|null
     */
    public function getSource(): ?SourceData
    {
        return isset($this->source) ? $this->source : null;
    }

    /**
     * @param SourceData|null $source
     * @return Attribute
     */
    public function setSource(?SourceData $source): Attribute
    {
        $this->source = $source;
        return $this;
    }
}
