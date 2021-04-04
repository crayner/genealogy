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
     * @var string
     * @ORM\Column(length=22)
     */
    private string $identifier;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\IndividualName", mappedBy="individual",cascade="persist")
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
     * @ORM\ManyToMany(targetEntity="event",cascade="persist")
     * @ORM\JoinTable(name="individual_events",
     *      joinColumns={@ORM\JoinColumn(name="individual_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")}
     *      )
     */
    private Collection $events;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=IndividualFamily::class, mappedBy="individual",cascade="persist")
     */
    private Collection $families;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity=SourceData::class,cascade="persist")
     */
    private Collection $sources;

    /**
     * @var string|null
     * @ORM\Column(length=22,nullable=true)
     */
    private ?string $recordKey;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $note;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity=MultimediaRecord::class,cascade="persist")
     * @ORM\JoinTable(name="individual_multimedia_records",
     *      joinColumns={@ORM\JoinColumn(name="individual_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="multimedia_record_id", referencedColumnName="id")}
     *      )
     */
    private Collection $multimediaRecords;

    /**
     * @var array|null
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private ?array $description;

    /**
     * Individual constructor.
     * @param string|null $identifier
     */
    public function __construct(?string $identifier = null)
    {
        if (!in_array($identifier, [null,''])) $this->identifier = $identifier;
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
        if (!isset($this->identifier)) throw new IndividualException('The individual does not have a valid identifier.');
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Individual
     */
    public function setIdentifier(string $identifier): Individual
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
     * @return string|null
     */
    public function getRecordKey(): ?string
    {
        return isset($this->recordKey) ? $this->recordKey : null;
    }

    /**
     * @param string|null $recordKey
     * @return Individual
     */
    public function setRecordKey(?string $recordKey): Individual
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
     * @return Individual
     */
    public function setNote(?string $note): Individual
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param string $note
     * @return Individual
     */
    public function concatNote(string $note): Individual
    {
        $this->note .= $note;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getMultimediaRecords(): Collection
    {
        return $this->multimediaRecords = isset($this->multimediaRecords) ? $this->multimediaRecords : new ArrayCollection();
    }

    /**
     * @param Collection $multimediaRecords
     * @return Individual
     */
    public function setMultimediaRecords(Collection $multimediaRecords): Individual
    {
        $this->multimediaRecords = $multimediaRecords;
        return $this;
    }

    /**
     * @param MultimediaRecord $record
     * @return Individual
     */
    public function addMultimediaRecord(MultimediaRecord $record): Individual
    {
        if ($this->getMultimediaRecords()->containsKey($record->getLink())) return $this;

        $this->multimediaRecords->set($record->getLink(), $record);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getDescription(): ?array
    {
        return isset($this->description) ? $this->description : null;
    }

    /**
     * @param array|null $description
     * @return Individual
     */
    public function setDescription(?array $description): Individual
    {
        $this->description = $description;
        return $this;
    }
}
