<?php

namespace App\Entity;

use App\Exception\SourceException;
use App\Repository\SourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Source
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 13:08
 * @ORM\Entity(repositoryClass=SourceRepository::class)
 * @ORM\Table(name="source")
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
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $authority;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $title;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $sourceText;

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
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $publish;

    /**
     * @var array
     * @ORM\Column(type="json",nullable=true)
     */
    private array $extra;

    /**
     * @var RepositoryRecord|null
     * @ORM\ManyToOne(targetEntity="App\Entity\RepositoryRecord",cascade="persist")
     * @ORM\JoinColumn(name="repository_record",nullable=true)
     */
    private ?RepositoryRecord $repositoryRecord;

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

    /**
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    /**
     * @param string|null $authority
     * @return Source
     */
    public function setAuthority(?string $authority): Source
    {
        if (mb_strlen($authority) > 248) throw new SourceException($this, sprintf('The authority (%s) is too long. The length must be less than or equal to 248 characters.', $authority));
        $this->authority = $authority;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Source
     */
    public function setTitle(?string $title): Source
    {
        if (mb_strlen($title) > 4095) throw new SourceException($this, sprintf('The title (%s) is too long. The length must be less than or equal to 4095 characters.', $title));
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSourceText(): ?string
    {
        return $this->sourceText;
    }

    /**
     * @param string|null $sourceText
     * @return Source
     */
    public function setSourceText(?string $sourceText): Source
    {
        if (mb_strlen($sourceText) > 32767) throw new SourceException($this, sprintf('The text (%s) is too long. The length must be less than or equal to 32767 characters.', $sourceText));
        $this->sourceText = $sourceText;
        return $this;
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
     * @return Source
     */
    public function setRecordKey(?string $recordKey): Source
    {
        if (mb_strlen($recordKey) > 12) throw new SourceException($this, sprintf('The text (%s) is too long. The length must be less than or equal to 12 characters.', $recordKey));
        $this->recordKey = $recordKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return isset($this->note) ? $this->note : null;
    }

    /**
     * @param string|null $note
     * @return Source
     */
    public function setNote(?string $note): Source
    {
        if (mb_strlen($note) < 1) return $this;
        $this->note = $note;
        return $this;
    }

    /**
     * @param string $note
     * @return Source
     */
    public function concatNote(string $note): Source
    {
        if (!isset($this->note) || is_null($this->note)) $this->note = '';

        $this->note .= $note;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPublish(): ?string
    {
        return $this->publish;
    }

    /**
     * @param string|null $publish
     * @return Source
     */
    public function setPublish(?string $publish): Source
    {
        if (mb_strlen($publish) > 4095) throw new SourceException($this, sprintf('The Publication Facts (%s) are too long. The length must be less than or equal to 4095 characters.', $publish));
        $this->publish = $publish;
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
     * @return Source
     */
    public function setExtra(array $extra): Source
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @param string $tag
     * @param string $content
     * @return Source
     */
    public function addExtra(string $tag, string $content): Source
    {
        $this->getExtra();
        $this->extra[$tag] = $content;
        return $this;
    }

    /**
     * @return RepositoryRecord|null
     */
    public function getRepositoryRecord(): ?RepositoryRecord
    {
        return isset($this->repositoryRecord) ? $this->repositoryRecord : null;
    }

    /**
     * @param RepositoryRecord|null $repositoryRecord
     * @return Source
     */
    public function setRepositoryRecord(?RepositoryRecord $repositoryRecord): Source
    {
        $this->repositoryRecord = $repositoryRecord;
        return $this;
    }
}
