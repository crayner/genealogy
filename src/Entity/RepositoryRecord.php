<?php

namespace App\Entity;

use App\Repository\RepositoryRecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class RepositoryRecord
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 11:21
 * @ORM\Entity(repositoryClass=RepositoryRecordRepository::class)
 * @ORM\Table(name="repository_record")
 */
class RepositoryRecord
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var string
     * @ORM\Column(length=22)
     */
    private string $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=90)
     */
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private ?string $recordKey;

    /**
     * RepositoryRecord constructor.
     * @param string|null $identifier
     */
    public function __construct(?string $identifier = null)
    {
        if (!in_array($identifier, [null,''])) $this->setIdentifier($identifier);
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
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return RepositoryRecord
     */
    public function setIdentifier(string $identifier): RepositoryRecord
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return RepositoryRecord
     */
    public function setName($name): RepositoryRecord
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRecordKey(): ?string
    {
        return isset($this->recordKey) ? $this->recordKey : null;
    }

    /**
     * @param string|null $recordKey
     * @return RepositoryRecord
     */
    public function setRecordKey(?string $recordKey): RepositoryRecord
    {
        $this->recordKey = $recordKey;
        return $this;
    }
}
