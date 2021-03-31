<?php

namespace App\Entity;

use App\Exception\PlaceException;
use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Place
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 31/03/2021 17:04
 * @ORM\Entity(repositoryClass=PlaceRepository::class)
 * @ORM\Table(name="place")
 */
class Place
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=120)
     */
    private string $name;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var string
     * @ORM\Column(type="enum");
     */
    private string $source;

    /**
     * @var array|string[]
     */
    private static array $sourceList = [
        'Event',
    ];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Place
     */
    public function setName(string $name): Place
    {
        if (mb_strlen($name) > 120) throw new PlaceException($this, sprintf('The place name (%s) must be <= 120 characters in length.',$name));
        $this->name = $name;
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
     * @return Place
     */
    public function setOffset(int $offset): Place
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Place
     */
    public function setSource(string $source): Place
    {
        if (!in_array($source,self::getSourceList())) throw new PlaceException($this, sprintf('The place source (%s) is not valid.',$source));
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
}
