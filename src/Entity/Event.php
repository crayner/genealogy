<?php

namespace App\Entity;

use App\Exception\EventException;
use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @ORM\Table(name="event")
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
     * @var DateTimeImmutable
     * @ORM\Column(type="date_immutable")
     */
    private DateTimeImmutable $date;

    /**
     * @var string
     * @ORM\Column(type="enum")
     */
    private string $eventSource;

    /**
     * @var array|string[]
     */
    private static array $eventSourceList = [
        'Individual',
        'Family',
    ];

    /**
     * @var string
     * @ORM\Column(type="enum")
     */
    private string $type;

    /**
     * @var array|string[] ]
     */
    private static $typeList = [
        'Birth',
    ];

    /**
     * @var Place
     * @ORM\OneToOne(targetEntity="App\Entity\Place")
     * @ORM\JoinColumn(name="place",nullable=true)
     */
    private ?Place $place;

    /**
     * @var int
     */
    private int $offset;

    /**
     * Event constructor.
     * @param string|null $eventSource
     */
    public function __construct(?string $eventSource = null)
    {
        if (!is_null($eventSource) ) $this->setEventSource($eventSource);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable $date
     * @return Event
     */
    public function setDate(DateTimeImmutable $date): Event
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventSource(): string
    {
        return $this->eventSource;
    }

    /**
     * @param string $eventSource
     * @return Event
     */
    public function setEventSource(string $eventSource): Event
    {
        if (!in_array($eventSource, self::getEventSourceList())) throw new EventException($this, sprintf('The event source (%s) is not valid', $eventSource));
        $this->eventSource = $eventSource;
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
        if (!in_array($type, self::getTypeList())) throw new EventException($this, sprintf('The event type (%s) is not valid.', $type));
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
}
