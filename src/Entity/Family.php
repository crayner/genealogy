<?php

namespace App\Entity;

use App\Exception\FamilyException;
use App\Repository\FamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Family
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 10:25
 * @ORM\Entity(repositoryClass=FamilyRepository::class)
 * @ORM\Table(name="family",uniqueConstraints={@ORM\UniqueConstraint(name="identifier",columns={"identifier"})})
 * @UniqueEntity("identifier")
 */
class Family
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
     * @var Collection
     * @ORM\ManyToMany(targetEntity=Event::class)
     * @ORM\JoinTable(name="family_events",
     *      joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")}
     *      )
     */
    private Collection $events;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=IndividualFamily::class, mappedBy="family")
     */
    private Collection $individuals;

    /**
     * @var string|null
     * @ORM\Column(length=22)
     */
    private ?string $recordKey;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private array $extra;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $note;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity=MultimediaRecord::class)
     * @ORM\JoinTable(name="family_multimedia_records",
     *      joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="multimedia_record_id", referencedColumnName="id")}
     *      )
     */
    private Collection $multimediaRecords;

    /**
     * Family constructor.
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
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Family
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events = isset($this->events) ? $this->events : new ArrayCollection();
    }

    /**
     * @param Collection $events
     * @return Family
     */
    public function setEvents(Collection $events): Family
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @param Event $event
     * @return Family
     */
    public function addEvent(Event $event): Family
    {
        if (!$this->getEvents()->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    /**
     * @param Event $event
     * @return Family
     */
    public function removeEvent(Event $event): Family
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * @return Collection|IndividualFamily[]
     */
    public function getIndividuals(): Collection
    {
        if (isset($this->individuals) && $this->individuals instanceof PersistentCollection) $this->individuals->initialize();

        return $this->individuals = isset($this->individuals) ? $this->individuals : new ArrayCollection();
    }

    /**
     * @param IndividualFamily $individual
     * @return Family
     */
    public function addIndividual(IndividualFamily $individual): Family
    {
        if (!$this->getIndividuals()->contains($individual)) {
            $this->individuals->add($individual);
            $individual->setFamily($this);
        }

        return $this;
    }

    /**
     * @param IndividualFamily $individual
     * @return Family
     */
    public function removeIndividual(IndividualFamily $individual): Family
    {
        if ($this->getIndividuals()->removeElement($individual)) {
            // set the owning side to null (unless already changed)
            if ($individual->getFamily() === $this) {
                $individual->setFamily(null);
            }
        }

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
     * @return Family
     */
    public function setRecordKey(?string $recordKey): Family
    {
        if (mb_strlen($recordKey) > 22) throw new FamilyException($this, sprintf('This record key (%s) is too long. It should have 22 character or less.',$recordKey));
        $this->recordKey = $recordKey;
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
     * @return Family
     */
    public function setExtra(array $extra): Family
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @param string $tag
     * @param string $content
     * @return Family
     */
    public function addExtra(string $tag, string $content): Family
    {
        $this->getExtra();
        $this->extra[$tag] = $content;

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
     * @return Family
     */
    public function setNote(string $note): Family
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param string $note
     * @return Family
     */
    public function concatNote(string $note): Family
    {
        $this->note = (isset($this->note) ? $this->note : '') . $note;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMultimediaRecords(): Collection
    {
        if (isset($this->multimediaRecords) && $this->multimediaRecords instanceof PersistentCollection) $this->multimediaRecords->initialize();

        return $this->multimediaRecords = isset($this->multimediaRecords) ? $this->multimediaRecords : new ArrayCollection();
    }

    /**
     * @param Collection $multimediaRecords
     * @return Family
     */
    public function setMultimediaRecords(Collection $multimediaRecords): Family
    {
        $this->multimediaRecords = $multimediaRecords;
        return $this;
    }

    /**
     * @param MultimediaRecord $record
     * @return Family
     */
    public function addMultimediaRecord(MultimediaRecord $record): Family
    {
        if ($this->getMultimediaRecords()->contains($record)) return $this;

        $this->multimediaRecords->add($record);

        return $this;
    }
}
