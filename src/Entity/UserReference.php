<?php

namespace App\Entity;

use App\Repository\UserReferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class UserReference
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 14:19
 * @ORM\Entity(repositoryClass=UserReferenceRepository::class)
 * @ORM\Table(name="user_reference")
 */
class UserReference
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
     * @ORM\Column(type="string", length=20)
     */
    private string $reference;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private ?string $type;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return UserReference
     */
    public function setReference(string $reference): UserReference
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return UserReference
     */
    public function setType(?string $type): UserReference
    {
        $this->type = $type;
        return $this;
    }
}
