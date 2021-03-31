<?php

namespace App\Entity;

use App\Exception\AttributeException;
use App\Repository\AttributeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Attribute
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 09:21
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 * @ORM\Table(name="attribute")
 */
class Attribute
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @ORM\Column(type="enum")
     */
    private $source;

    /**
     * @var array|string[]
     */
    private static array $sourceList = [
        'Individual',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=120, nullable=true)
     */
    private $email;

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
     * Attribute constructor.
     * @param string|null $source
     */
    public function __construct(?string $source = null)
    {
        if (!is_null($source)) $this->setSource($source);
    }

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
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     * @return Attribute
     */
    public function setSource($source)
    {
        if (!in_array($source, self::getSourceList())) throw new AttributeException($this, sprintf('The attribute source (%s) is not valid.', $source));
        $this->source = $source;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getSourceList(): array
    {
        return self::$sourceList;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return isset($this->email) ? $this->email : null;
    }

    /**
     * @param string $email
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
}
