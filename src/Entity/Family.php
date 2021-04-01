<?php

namespace App\Entity;

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
     * @var int
     * @ORM\Column(type="smallint")
     */
    private int $identifier;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity=Event::class)
     * @ORM\JoinTable(name="family_events",
     *      joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")}
     *      )
     */
    private $events;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=IndividualFamily::class, mappedBy="family")
     */
    private $individuals;

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
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @param int $identifier
     * @return Family
     */
    public function setIdentifier(int $identifier): Family
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
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
        if (!$this->events->contains($event)) {
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
}
