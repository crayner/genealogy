<?php
namespace App\Entity;

use App\Manager\IndividualNameManager;
use App\Repository\IndividualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Class Individual
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @selectPure App\Entity
 */
#[ORM\Entity(repositoryClass: IndividualRepository::class)]
#[ORM\Table(name: 'individual', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['mother'], name: 'mother')]
#[ORM\Index(columns: ['father'], name: 'father')]
#[ORM\UniqueConstraint(name: 'source', columns: ['source_ID'])]
#[ORM\UniqueConstraint(name: 'userID', columns: ['user_ID'])]
#[ORM\UniqueConstraint(name: 'userIDDB', columns: ['user_ID_DB'])]
class Individual
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    private int $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned'])]
    private int $source_ID;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $user_ID;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $user_ID_DB;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $last_Touched;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $created_On;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer', options: ['unsigned'])]
    private int $edit_Count;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $prefix;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $first_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $preferred_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $middle_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $nick_Names;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $last_Name_At_Birth;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $last_Name_Current;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $last_Name_Other;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $suffix;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    private ?string $gender;

    /**
     * @var array|string[]
     */
    static public array $genderList =
        [
            'Unknown',
            'Male',
            'Female',
        ];

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $birth_Date;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $death_Date;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $birth_Location;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $death_Location;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo;

    /**
     * @var Individual|null
     */
    #[ORM\ManyToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'father')]
    private ?Individual $father;

    /**
     * @var Individual|null
     */
    #[ORM\ManyToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'mother')]
    private ?Individual $mother;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer', options: ['unsigned'])]
    private int $page_ID;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Individual::class, inversedBy: 'profiles')]
    #[ORM\JoinTable(name: 'profiles_managers')]
    #[ORM\JoinColumn(name: 'profile', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'manager', referencedColumnName: 'id')]
    private Collection $managers;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Individual::class, mappedBy: 'managers')]
    #[ORM\JoinTable(name: 'profiles_managers')]
    #[ORM\JoinColumn(name: 'manager', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'profile', referencedColumnName: 'id')]
    private Collection $profiles;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_Living', type: 'boolean')]
    private bool $living;

    /**
     * @var int
     * bit 0 : Edit = 1, no edit = 0
     * bit 1 : Biography: Public = 1, Private = 0
     * bit 2 : Family Tree: Public = 1, Private = 0
     */
    #[ORM\Column(type: 'smallint')]
    private int $privacy;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $background;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $thank_Count;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_Locked', type: 'boolean')]
    private bool $locked;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_Guest', type: 'boolean')]
    private bool $guest;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_Connected', type: 'boolean')]
    private bool $connected;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $marriages;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'individuals', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'individual_category')]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'individual', referencedColumnName: 'id')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[MaxDepth(2)]
    private Collection $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->managers = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->marriages = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Individual
     */
    public function setId(?int $id): Individual
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getSourceID(): int
    {
        return $this->source_ID ?? 0;
    }

    /**
     * @param int $source_ID
     * @return Individual
     */
    public function setSourceID(int $source_ID): Individual
    {
        $this->source_ID = $source_ID;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserID(): string
    {
        return $this->user_ID;
    }

    /**
     * @param string $user_ID
     * @return Individual
     */
    public function setUserID(string $user_ID): Individual
    {
        $this->user_ID = $user_ID;
        return $this;
    }

    /**
     * @param bool $reflectNull
     * @return string|null
     */
    public function getUserIDDB(bool $reflectNull = false): ?string
    {
        if ($reflectNull) return $this->user_ID_DB;
        return !empty($this->user_ID_DB) ? $this->user_ID_DB : $this->getUserID();
    }

    /**
     * @param string|null $user_ID_DB
     * @return Individual
     */
    public function setUserIDDB(?string $user_ID_DB): Individual
    {
        $this->user_ID_DB = $user_ID_DB;
        return $this;
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function wastedSpace(LifecycleEventArgs $args)
    {
        $this->setUserIDDB(str_replace(["_"], ' ', $this->getUserIDDB()));
        if ($this->getUserID() === $this->getUserIDDB() || empty($this->user_ID_DB)) {
            $this->setUserIDDB(null);
        }

        if (!isset($this->edit_Count) || $this->edit_Count < 1) $this->setEditCount(1);

        if ($this->getLastNameCurrent() === $this->getLastNameAtBirth()) {
            $this->setLastNameCurrent(null);
        }

        if (empty($this->getFirstName()) && !empty($this->getPreferredName())) {
            $this->setFirstName($this->getPreferredName())
                ->setPreferredName(null);
        }

        if ($this->getPrivacy() > 7) {
            switch ($this->getPrivacy()) {
                case 20:
                    $this->setPrivacy(0);
                    break;
                case 30:
                    $this->setPrivacy(2);
                    break;
                case 35:
                    $this->setPrivacy(4);
                    break;
                case 40:
                    $this->setPrivacy(6);
                    break;
                case 14:
                case 50:
                    $this->setPrivacy(14);
                    break;
                case 15:
                case 60:
                    $this->setPrivacy(15);
                    break;
                default:
                    dd($this->getPrivacy(), $this);
            }
        }
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastTouched(): ?\DateTimeImmutable
    {
        return $this->last_Touched;
    }

    /**
     * @param \DateTimeImmutable|null $last_Touched
     * @return Individual
     */
    public function setLastTouched(?\DateTimeImmutable $last_Touched): Individual
    {
        $this->last_Touched = $last_Touched;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->created_On;
    }

    /**
     * @param \DateTimeImmutable|null $created_On
     * @return Individual
     */
    public function setCreatedOn(?\DateTimeImmutable $created_On): Individual
    {
        $this->created_On = $created_On;
        return $this;
    }

    /**
     * @return int
     */
    public function getEditCount(): int
    {
        if (!isset($this->edit_Count) || $this->edit_Count < 1) $this->setEditCount(1);
        return $this->edit_Count;
    }

    /**
     * @param int $edit_Count
     * @return Individual
     */
    public function setEditCount(int $edit_Count): Individual
    {
        $this->edit_Count = $edit_Count;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     * @return Individual
     */
    public function setPrefix(?string $prefix): Individual
    {
        $this->prefix = $prefix === '' ? null : $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_Name;
    }

    /**
     * @param string $first_Name
     * @return Individual
     */
    public function setFirstName(string $first_Name): Individual
    {
        $this->first_Name = $first_Name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreferredName(): ?string
    {
        return !is_null($this->preferred_Name) ? $this->preferred_Name : $this->getFirstName();
    }

    /**
     * @param string|null $preferred_Name
     * @return Individual
     */
    public function setPreferredName(?string $preferred_Name): Individual
    {
        $this->preferred_Name = $preferred_Name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middle_Name;
    }

    /**
     * @param string|null $middle_Name
     * @return Individual
     */
    public function setMiddleName(?string $middle_Name): Individual
    {
        $this->middle_Name = $middle_Name === "" ? null : $middle_Name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNickNames(): ?string
    {
        return $this->nick_Names;
    }

    /**
     * @param string|null $nick_Names
     * @return Individual
     */
    public function setNickNames(?string $nick_Names): Individual
    {
        $this->nick_Names = $nick_Names === "" ? null : $nick_Names;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastNameAtBirth(): ?string
    {
        return $this->last_Name_At_Birth;
    }

    /**
     * @param string|null $last_Name_At_Birth
     * @return Individual
     */
    public function setLastNameAtBirth(?string $last_Name_At_Birth): Individual
    {
        $this->last_Name_At_Birth = $last_Name_At_Birth;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastNameCurrent(): ?string
    {
        return !is_null($this->last_Name_Current) ? $this->last_Name_Current : $this->getLastNameAtBirth();
    }

    /**
     * @param string|null $last_Name_Current
     * @return Individual
     */
    public function setLastNameCurrent(?string $last_Name_Current): Individual
    {
        $this->last_Name_Current = $last_Name_Current;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastNameOther(): ?string
    {
        return $this->last_Name_Other;
    }

    /**
     * @param string|null $last_Name_Other
     * @return Individual
     */
    public function setLastNameOther(?string $last_Name_Other): Individual
    {
        $this->last_Name_Other = $last_Name_Other === '' ? null : $last_Name_Other;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string|null $suffix
     * @return Individual
     */
    public function setSuffix(?string $suffix): Individual
    {
        $this->suffix = empty($suffix) ? null : $suffix;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     * @return Individual
     */
    public function setGender(?string $gender): Individual
    {
        $this->gender = in_array($gender, static::getGenderList(true)) ? $gender : null;
        return $this;
    }

    /**
     * @param bool $withNull
     * @return array|string[]
     */
    static public function getGenderList(bool $withNull = false): array
    {
        return $withNull ? array_merge([null], static::$genderList) : static::$genderList;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birth_Date;
    }

    /**
     * @param \DateTimeImmutable|null $birth_Date
     * @return Individual
     */
    public function setBirthDate(?\DateTimeImmutable $birth_Date): Individual
    {
        $this->birth_Date = $birth_Date;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeathDate(): ?\DateTimeImmutable
    {
        return $this->death_Date;
    }

    /**
     * @param \DateTimeImmutable|null $death_Date
     * @return Individual
     */
    public function setDeathDate(?\DateTimeImmutable $death_Date): Individual
    {
        $this->death_Date = $death_Date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBirthLocation(): ?string
    {
        return $this->birth_Location;
    }

    /**
     * @param string|null $birth_Location
     * @return Individual
     */
    public function setBirthLocation(?string $birth_Location): Individual
    {
        $this->birth_Location = substr(empty($birth_Location) ? null : $birth_Location, 0, 255);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeathLocation(): ?string
    {
        return $this->death_Location;
    }

    /**
     * @param string|null $death_Location
     * @return Individual
     */
    public function setDeathLocation(?string $death_Location): Individual
    {
        $this->death_Location = substr(empty($death_Location) ? null : $death_Location, 0, 255);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param string|null $photo
     * @return Individual
     */
    public function setPhoto(?string $photo): Individual
    {
        $this->photo = empty($photo) ? null : $photo;
        return $this;
    }

    /**
     * @return Individual|null
     */
    public function getFather(): ?Individual
    {
        return $this->father;
    }

    /**
     * @param Individual|null $father
     * @return $this
     */
    public function setFather(?Individual $father): Individual
    {
        $this->father = $father;
        return $this;
    }

    /**
     * @return Individual|null
     */
    public function getMother(): ?Individual
    {
        return $this->mother;
    }

    /**
     * @param Individual|null $mother
     * @return Individual
     */
    public function setMother(?Individual $mother): Individual
    {
        $this->mother = $mother;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageID(): int
    {
        return $this->page_ID;
    }

    /**
     * @param int $page_ID
     * @return $this
     */
    public function setPageID(int $page_ID): Individual
    {
        $this->page_ID = $page_ID;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    /**
     * @param Collection $managers
     * @return Individual
     */
    public function setManagers(Collection $managers): Individual
    {
        $this->managers = $managers;
        return $this;
    }

    /**
     * @param Individual|null $manager
     * @return Individual
     */
    public function addManager(?Individual $manager): Individual
    {
        if (is_null($manager) || $this->getManagers()->contains($manager)) return $this;
        $this->getManagers()->add($manager);
        $manager->addProfile($this);
        return $this;
    }

    /**
     * @param Individual $manager
     * @return $this
     */
    public function removeManager(Individual $manager): Individual
    {
        if ($this->getManagers()->contains($manager)) {
            $this->getManagers()->removeElement($manager);
            $manager->removeProfile($this);
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    /**
     * @param Collection $profiles
     * @return Individual
     */
    public function setProfiles(Collection $profiles): Individual
    {
        $this->profiles = $profiles;
        return $this;
    }

    /**
     * @param Individual|null $profile
     * @return Individual
     */
    public function addProfile(?Individual $profile): Individual
    {
        if (is_null($profile) || $this->getProfiles()->contains($profile)) return $this;
        $this->getProfiles()->add($profile);
        $profile->addManager($this);
        return $this;
    }

    /**
     * @param Individual $profile
     * @return $this
     */
    public function removeProfile(Individual $profile): Individual
    {
        if ($this->getProfiles()->contains($profile)) {
            $this->getProfiles()->removeElement($profile);
            $profile->removeManager($this);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isLiving(): bool
    {
        return $this->living;
    }

    /**
     * @param bool $living
     * @return Individual
     */
    public function setLiving(bool $living): Individual
    {
        $this->living = $living;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrivacy(): int
    {
        return $this->privacy;
    }

    /**
     * @param int $privacy
     * @return Individual
     */
    public function setPrivacy(int $privacy): Individual
    {
        $this->privacy = $privacy;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackground(): ?string
    {
        return $this->background;
    }

    /**
     * @param string|null $background
     * @return Individual
     */
    public function setBackground(?string $background): Individual
    {
        $this->background = empty($background) ? null : $background;
        return $this;
    }

    /**
     * @return int
     */
    public function getThankCount(): int
    {
        return $this->thank_Count;
    }

    /**
     * @param int $thank_Count
     * @return Individual
     */
    public function setThankCount(int $thank_Count): Individual
    {
        $this->thank_Count = empty($thank_Count) ? 0 : $thank_Count;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return Individual
     */
    public function setLocked(bool $locked): Individual
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->guest;
    }

    /**
     * @param bool $guest
     * @return Individual
     */
    public function setGuest(bool $guest): Individual
    {
        $this->guest = $guest;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @param bool $connected
     * @return Individual
     */
    public function setConnected(bool $connected): Individual
    {
        $this->connected = $connected;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function createEventDate(?string $date): ?\DateTimeImmutable
    {
        if (empty($date) || strlen($date) !== 8) return null;
        $hour = '00';
        $min = '00';
        $sec = '00';
        $year = substr($date, 0, 4);
        $month = substr($date, 4,2);
        $day = substr($date, 6, 2);
        if ($year > 0) {
            $hour = '01';
        } else {
            return null;
        }
        if ($month >= 1 && $month <= 12) {
            $min = '01';
            if ($day >= 1 && $day <= 31) {
                $sec = '01';
            } else {
                $day = '01';
            }
        } else {
            $month = '01';
            $day = '01';
        }
        $date = $year . $month . $day . " " . $hour . $min . $sec;

        return new \DateTimeImmutable($date, new \DateTimeZone("UTC"));
    }

    /**
     * @param \DateTimeImmutable|null $date
     * @param bool $decade
     * @return string
     */
    public function parseEventDate(?\DateTimeImmutable $date, bool $decade = false): string
    {
        if (is_null($date)) return '';
        $yearValid = (bool)intval($date->format('G'));
        if (!$yearValid) return '';
        if ($decade) {
            $year = $date->format('Y');
            while ((int)$year % 10 !== 0) {
                $year -= 1;
            }
            return $year;
        }
        $monthValid = (bool)intval($date->format('i'));
        $dayValid = (bool)intval($date->format('s'));
        if ($dayValid) {
            return $date->format('j M Y');
        }
        if ($monthValid) {
            return $date->format('M Y');

        }
        return $date->format('Y');
    }

    /**
     * @return string
     */
    public function getBirthDateFirstNameString(): string
    {
        $result = $this->getBirthDate() === null ? '' : $this->getBirthDate()->format('Ymd');
        $result .= $this->getFirstName();
        return $result;
    }

    /**
     * @return array
     */
    public function getAge(): array
    {
        $result = [];
        if ($this->getBirthDate() instanceof \DateTimeImmutable && $this->getDeathDate() instanceof \DateTimeImmutable) {
            $diff = $this->getDeathDate()->diff($this->getBirthDate());
            if ($this->getBirthDate()->format('G') === '1' && $this->getDeathDate()->format('G') === '1') {
                $result['{y}'] = $diff->y;
                if ($this->getBirthDate()->format('i') === '01' && $this->getDeathDate()->format('i') === '01') {
                    $result['{m}'] = $diff->m;
                    if ($this->getBirthDate()->format('s') === '01' && $this->getDeathDate()->format('s') === '01') {
                         $result['{d}'] = $diff->d;
                         $result['status'] = 'full';
                    } else {
                        $result['status'] = 'year_mon';
                    }
                } else {
                    $result['status'] = 'year_only';
                }
            }
        } else {
            $result['status'] = 'no_age';
        }
        return $result;
    }

    /**
     * @return ArrayCollection
     */
    public function getMarriages(): ArrayCollection
    {

        return $this->marriages = $this->marriages ?? new ArrayCollection();
    }

    /**
     * @param ArrayCollection $marriages
     * @return Individual
     */
    public function setMarriages(ArrayCollection $marriages): Individual
    {
        $this->marriages = $marriages;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     * @return Individual
     */
    public function setCategories(Collection $categories): Individual
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category): Individual
    {
        if ($this->getCategories()->contains($category)) return $this;
        $this->getCategories()->add($category);
        $category->addIndividual($this);
        return $this;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getShortName(),
            'birthDate' => $this->getNameManager()->getShortEventDate($this, 'birth'),
            'birthLocation' => $this->getBirthLocation(),
            'deathDate' => $this->getNameManager()->getShortEventDate($this, 'death'),
            'deathLocation' => $this->getDeathLocation(),
            'anchorKey' => strtoupper(mb_substr($this->getLastNameAtBirth(), 0, 1)),
        ];
    }

    /**
     * @return string
     */
    protected function getShortName(): string
    {
        return $this->getNameManager()->getShortName($this);
    }

    /**
     * @var IndividualNameManager
     */
    private IndividualNameManager $nameManager;

    /**
     * @return IndividualNameManager
     */
    protected function getNameManager(): IndividualNameManager
    {
        return $this->nameManager = $this->nameManager ?? new IndividualNameManager();
    }

    public function isValid(): bool
    {
        if (isset($this->id)) return true;
        if (isset($this->first_Name) && isset($this->last_Name_At_Birth)) return true;
        return false;
    }
}
