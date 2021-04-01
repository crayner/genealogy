<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: Craig Rayner
 * Date: 30/03/2021
 * Time: 08:57
 */

namespace App\Entity;

use App\Exception\IndividualException;
use App\Repository\IndividualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Individual
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @ORM\Entity(repositoryClass=IndividualRepository::class)
 * @ORM\Table(name="individual",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="identifier",columns={"identifier"})})
 * @UniqueEntity("identifier")
 */
class Individual
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
     * @ORM\OneToMany(targetEntity="App\Entity\IndividualName", mappedBy="individual")
     */
    private Collection $names;

    /**
     * @var string
     * @ORM\Column(type="enum",options={"default": "N"})
     */
    private string $gender;

    /**
     * @var array|string[]
     */
    private static array $genderList = [
        'M',  // Male
        'F',  // Female
        'X',  // Intersex
        'U',  // Unknown (not found yet)
        'N',  // Not recorded
    ];

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="event")
     * @ORM\JoinTable(name="individual_events",
     *      joinColumns={@ORM\JoinColumn(name="individual_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")}
     *      )
     */
    private Collection $events;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=IndividualFamily::class, mappedBy="individual")
     */
    private Collection $families;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity=SourceData::class)
     */
    private Collection $sources;

    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    private int $recordKey;

    /**
     * Individual constructor.
     * @param int $identifier
     */
    public function __construct(int $identifier = 0)
    {
        if ($identifier > 0) $this->identifier = $identifier;
        $this->sources = new ArrayCollection();
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
        if (!isset($this->identifier)) throw new IndividualException('The individual does not have a valid identifier.');
        return $this->identifier;
    }

    /**
     * @param int $identifier
     * @return Individual
     */
    public function setIdentifier(int $identifier): Individual
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNames(): Collection
    {
        if (isset($this->names) && $this->names instanceof PersistentCollection) $this->names->initialize();

        return $this->names = isset($this->names) ? $this->names : new ArrayCollection();
    }

    /**
     * @param Collection $names
     * @return Individual
     */
    public function setNames(Collection $names): Individual
    {
        $this->names = $names;
        return $this;
    }

    /**
     * @param IndividualName $name
     * @return Individual
     */
    public function addName(IndividualName $name, bool $mirror = true): Individual
    {
        if ($mirror) $name->setIndividual($this, false);
        if ($this->getNames()->contains($name)) return $this;

        $this->names->add($name);

        return $this;
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return Individual
     */
    public function setGender(string $gender): Individual
    {
        if (!in_array($gender, self::getGenderList())) throw new IndividualException($this, sprintf('The gender (%s) given for the individual is not valid.', $gender));
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getGenderList(): array
    {
        return self::$genderList;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        if (isset($this->events) && $this->events instanceof PersistentCollection) $this->events->initialize();

        return $this->events = isset($this->events) ? $this->events : new ArrayCollection();
    }

    /**
     * @param Collection $events
     * @return Individual
     */
    public function setEvents(Collection $events): Individual
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @param Event $event
     * @return Individual
     */
    public function addEvent(Event $event): Individual
    {
        if ($this->getEvents()->contains($event)) return $this;

        $this->events->add($event);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFamilies(): Collection
    {
        if (isset($this->families) && $this->families instanceof PersistentCollection) $this->families->initialize();

        return $this->families = isset($this->families) ? $this->families : new ArrayCollection();
    }

    /**
     * @param Collection $families
     * @return Individual
     */
    public function setFamilies(Collection $families): Individual
    {
        $this->families = $families;
        return $this;
    }

    /**
     * @param IndividualFamily $family
     * @return Individual
     */
    public function addFamily(IndividualFamily $family): Individual
    {
        if (!$this->getFamilies()->contains($family)) {
            $this->families->add($family);
            $family->setIndividual($this);
        }

        return $this;
    }

    /**
     * @param IndividualFamily $family
     * @return Individual
     */
    public function removeFamily(IndividualFamily $family): Individual
    {
        if ($this->getFamilies()->removeElement($family)) {
            // set the owning side to null (unless already changed)
            if ($family->getIndividual() === $this) {
                $family->setIndividual(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSources(): Collection
    {
        if (isset($this->sources) && $this->sources instanceof PersistentCollection) $this->sources->initialize();

        return $this->sources = isset($this->sources) ? $this->sources : new ArrayCollection();
    }

    /**
     * @param SourceData $source
     * @return Individual
     */
    public function addSource(SourceData $source): Individual
    {
        if (!$this->getSources()->contains($source)) {
            $this->sources->add($source);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getRecordKey(): int
    {
        return $this->recordKey;
    }

    /**
     * @param int $recordKey
     * @return Individual
     */
    public function setRecordKey(int $recordKey): Individual
    {
        $this->recordKey = $recordKey;
        return $this;
    }
}
