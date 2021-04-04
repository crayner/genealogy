<?php

namespace App\Entity;

use App\Exception\SourceDataException;
use App\Repository\DataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Data
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 15:56
 * @ORM\Entity(repositoryClass=DataRepository::class)
 * @ORM\Table(name="data")
 */
class Data
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private \DateTimeImmutable $date;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private string $content;

    /**
     * @var SourceData
     * @ORM\OneToOne(targetEntity=SourceData::class,inversedBy="data")
     */
    private SourceData $sourceData;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return Data
     */
    public function setDate(\DateTimeImmutable $date): Data
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Data
     */
    public function setContent(string $content): Data
    {
        if (mb_strlen($content) > 32767) throw new SourceDataException($this->getSourceData(), sprintf('The text field must be less than 32768 characters in length.'));
        $this->content = $content;
        return $this;
    }

    /**
     * @return SourceData
     */
    public function getSourceData(): SourceData
    {
        return $this->sourceData;
    }

    /**
     * @param SourceData $sourceData
     * @param bool $mirror
     * @return Data
     */
    public function setSourceData(SourceData $sourceData, bool $mirror = true): Data
    {
        $this->sourceData = $sourceData;
        if ($mirror) $this->sourceData->setData($this, false);
        return $this;
    }
}
