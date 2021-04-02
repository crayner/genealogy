<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SourceRepository::class)
 */
class Source
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
     * @ORM\Column(length=22)
     */
    private string $identifier;

    /**
     * Source constructor.
     * @param int $identifier
     */
    public function __construct(?string $identifier = null)
    {
        if (!is_null($identifier)) $this->setIdentifier($identifier);
    }

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
    public function getIdentifier(): string
    {
        return $this->identifier = isset($this->identifier) ? $this->identifier : mb_substr(uniqid('SOUR_', true), 0, 22);
    }

    /**
     * @param string $identifier
     * @return Source
     */
    public function setIdentifier(string $identifier): Source
    {
        $this->identifier = $identifier;
        return $this;
    }
}
