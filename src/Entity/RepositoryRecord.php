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
 * @ORM\JoinTable(name="repository_record")
 */
class RepositoryRecord
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @ORM\Column(type="string", length=90)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $recordKey;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return RepositoryRecord
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecordKey()
    {
        return $this->recordKey;
    }

    /**
     * @param mixed $recordKey
     * @return RepositoryRecord
     */
    public function setRecordKey($recordKey)
    {
        $this->recordKey = $recordKey;
        return $this;
    }
}
