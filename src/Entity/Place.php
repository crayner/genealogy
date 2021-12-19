<?php

namespace App\Entity;

use App\Exception\PlaceException;
use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

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
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)    
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
}
