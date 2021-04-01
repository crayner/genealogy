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
     * @var int
     * @ORM\Column(type="smallint")
     */
    private int $identifier;

    /**
     * Source constructor.
     * @param int $identifier
     */
    public function __construct(int $identifier = 0)
    {
        if ($identifier > 0) $this->setIdentifier($identifier);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @return Source
     */
    public function setIdentifier($identifier): Source
    {
        $this->identifier = $identifier;
        return $this;
    }
}
