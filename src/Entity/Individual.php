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
use PhpParser\ErrorHandler\Collecting;

/**
 * Class Individual
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @ORM\Entity(repositoryClass=IndividualRepository::class)
 * @ORM\Table(name="individual")
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
     * @var IndividualName
     * @ORM\OneToOne(targetEntity="App\Entity\IndividualName", inversedBy="individual")
     */
    private IndividualName $name;

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
     * Individual constructor.
     * @param int $identifier
     */
    public function __construct(int $identifier = 0)
    {
        if ($identifier > 0) $this->identifier = $identifier;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Individual
     */
    public function setId(?string $id): Individual
    {
        $this->id = $id;
        return $this;
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
     * @return IndividualName
     */
    public function getName(): IndividualName
    {
        return $this->name = isset($this->name) ? $this->name : new IndividualName();
    }

    /**
     * @param IndividualName $name
     * @param bool $mirror
     * @return Individual
     */
    public function setName(IndividualName $name, bool $mirror = true): Individual
    {
        $this->name = $name;
        if ($mirror) $this->name->setIndividual($this, false);
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
        if (!in_array($gender, self::getGenderList())) throw new IndividualException(sprintf('The gender (%s) given for the individual is not valid.', $gender));
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
}
