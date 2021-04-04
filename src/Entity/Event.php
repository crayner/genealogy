<?php

namespace App\Entity;

use App\Exception\EventException;
use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @ORM\Table(name="event",
 *     indexes={@ORM\Index(name="source",columns={"source"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="place",columns={"place"})}
 *     )
 */
class Event
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private ?DateTimeImmutable $date;

    /**
     * @var string|null
     * @ORM\Column(length=90,nullable=true)
     */
    private ?string $name;

    /**
     * @var string
     * @ORM\Column(type="enum",length=191,options={"default": "Not Stated"})
     */
    private string $type = 'Not Stated';

    /**
     * @var array|string[] ]
     */
    private static array $typeList = [
        'Birth',
        'Death',
        'Buried',
        'Marriage',
        'Baptism',
        'Immigration',
        'Divorce',
        'Occupation',
        'Education',
        'Christening',
        'Engagement',
    ];

    /**
     * @var Place
     * @ORM\OneToOne(targetEntity="App\Entity\Place",cascade="persist")
     * @ORM\JoinColumn(name="place",nullable=true)
     */
    private ?Place $place;

    /**
     * @var string|null
     * @ORM\Column(length=13,nullable=true)
     */
    private ?string $age;

    /**
     * @var string|null
     * @ORM\Column(length=90,nullable=true)
     */
    private ?string $cause;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $note;

    /**
     * @var string|null
     * @ORM\Column(length=27,nullable=true)
     */
    private ?string $role;

    /**
     * @var array
     */
    private static array $roleList = [
        'CHIL',
        'HUSB',
        'WIFE',
        'MOTH',
        'FATH',
        'SPOU'
    ];

    /**
     * @var SourceData|null
     * @ORM\ManyToOne(targetEntity=SourceData::class,cascade="persist")
     * @ORM\JoinColumn(name="source",nullable=true)
     */
    private ?SourceData $source;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return isset($this->date) ? $this->date : null;
    }

    /**
     * @param DateTimeImmutable|null $date
     * @return Event
     */
    public function setDate(?DateTimeImmutable $date): Event
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return isset($this->name) ? $this->name : null;
    }

    /**
     * @param string|null $name
     * @return Event
     */
    public function setName(?string $name): Event
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getEventSourceList(): array
    {
        return self::$eventSourceList;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Event
     */
    public function setType(string $type): Event
    {
        if (preg_match('/arrival/i', $type)) $type = 'Immigration';
        if (!in_array($type, self::getTypeList())) throw new EventException($this, sprintf('The event type (%s) must be one of [%s].', $type, implode(', ', self::getTypeList())));
        $this->type = $type;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return Place
     */
    public function getPlace(): ?Place
    {
        return isset($this->place) ? $this->place : null;
    }

    /**
     * @param Place $place
     * @return Event
     */
    public function setPlace(?Place $place): Event
    {
        $this->place = $place;
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
     * @return Event
     */
    public function setOffset(int $offset): Event
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAge(): ?string
    {
        return isset($this->age) ? $this->age : null;
    }

    /**
     * @param string|null $age
     * @return Event
     */
    public function setAge(?string $age): Event
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCause(): ?string
    {
        return isset($this->cause) ? $this->cause : null;
    }

    /**
     * @param string|null $cause
     * @return Event
     */
    public function setCause(?string $cause): Event
    {
        $this->cause = $cause;
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
     * @return Event
     */
    public function setNote(?string $note): Event
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return isset($this->role) ? $this->role : null;
    }

    /**
     * @param string|null $role
     * @return Event
     */
    public function setRole(?string $role): Event
    {
        if (!in_array($role, self::getRoleList()) && !is_null($role)) $role = '('.trim($role, '()').')';
        if (mb_strlen($role) > 27) throw new EventException($this, sprintf('The event role [%s] must be less than 27 characters in length.',$role));
        $this->role = $role;
        return $this;
    }

    /**
     * @return array
     */
    public static function getRoleList(): array
    {
        return self::$roleList;
    }

    /**
     * @return SourceData|null
     */
    public function getSource(): ?SourceData
    {
        return isset($this->source) ? $this->source : null;
    }

    /**
     * @param SourceData|null $source
     * @return Event
     */
    public function setSource(?SourceData $source): Event
    {
        $this->source = $source;
        return $this;
    }
}
