<?php

namespace App\Entity;

use App\Exception\MultiMediaRecordException;
use App\Repository\MultimediaRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


/**
 * Class MultimediaRecord
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 14:26
 * @ORM\Entity(repositoryClass=MultimediaRecordRepository::class)
 * @ORM\Table(name="multimedia_record")
 */
class MultimediaRecord
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
     * @ORM\Column(length=36,nullable=true)
     */
    private string $link;

    /**
     * @var MultimediaFile|null
     * @ORM\ManyToOne(targetEntity=MultimediaFile::class,cascade={"persist"})
     * @ORM\JoinTable(name="multimedia_record_filr",
     *      joinColumns={@ORM\JoinColumn(name="record_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private ?MultimediaFile $fileReference;

    /**
     * @var string|null
     * @ORM\Column(length=12,nullable=true)
     */
    private ?string $recordKey;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $note;

    /**
     * @var SourceData|null
     * @ORM\OneToOne(targetEntity=SourceData::class)
     * @ORM\JoinColumn(name="source")
     */
    private ?SourceData $source;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private ?\DateTimeImmutable $changeDate;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private array $extra = [];

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
    public function getLink(): string
    {
        return $this->link = isset($this->link) ? $this->link : mb_substr(uniqid('OBJE_', true), 0, 22);
    }

    /**
     * @param string $link
     * @return MultimediaRecord
     */
    public function setLink(string $link): MultimediaRecord
    {
        if (mb_strlen($link) > 22) throw new MultiMediaRecordException($this, sprintf('This link value (%s) is too long. It should have 22 character or less.',$link));
        $this->link = $link;
        return $this;
    }

    /**
     * @return MultimediaFile|null
     */
    public function getFileReference(): ?MultimediaFile
    {
        return $this->fileReference = isset($this->fileReference) ? $this->fileReference : null;
    }

    /**
     * @param MultimediaFile|null $fileReference
     * @return MultimediaRecord
     */
    public function setFileReference(?MultimediaFile $fileReference): MultimediaRecord
    {
        $this->fileReference = $fileReference;
        return $this;
    }

    /**
     * @return MultimediaFile
     */
    public function createFileReference(): MultimediaFile
    {
        return $this->fileReference = isset($this->fileReference) ? $this->fileReference : new MultimediaFile();
    }

    /**
     * @return string|null
     */
    public function getRecordKey(): ?string
    {
        return $this->recordKey;
    }

    /**
     * @param string|null $recordKey
     * @return MultimediaRecord
     */
    public function setRecordKey(?string $recordKey): MultimediaRecord
    {
        $this->recordKey = $recordKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     * @return MultimediaRecord
     */
    public function setNote(?string $note): MultimediaRecord
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return SourceData|null
     */
    public function getSource(): ?SourceData
    {
        return $this->source;
    }

    /**
     * @param SourceData|null $source
     * @return MultimediaRecord
     */
    public function setSource(?SourceData $source): MultimediaRecord
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->changeDate;
    }

    /**
     * @param \DateTimeImmutable|null $changeDate
     * @return MultimediaRecord
     */
    public function setChangeDate(?\DateTimeImmutable $changeDate): MultimediaRecord
    {
        $this->changeDate = $changeDate;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra = isset($this->extra) ? $this->extra : [];
    }

    /**
     * @param array $extra
     * @return MultimediaRecord
     */
    public function setExtra(array $extra): MultimediaRecord
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @param string $tag
     * @param string $content
     * @return MultimediaRecord
     */
    public function addExtra(string $tag, string $content): MultimediaRecord
    {
        $this->getExtra();
        $this->extra[$tag] = $content;

        return $this;
    }
}
