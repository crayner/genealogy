<?php
namespace App\Entity;

use App\Repository\IndividualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class Individual
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @selectPure App\Entity
 */
#[ORM\Entity(repositoryClass: IndividualRepository::class)]
#[ORM\Table(name: 'individual', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\HasLifecycleCallbacks]
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
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $user_ID;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
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
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $prefix;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 128)]
    private string $first_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $preferred_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $middle_Name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $nick_Names;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128)]
    private ?string $last_Name_At_Birth;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $last_Name_Current;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $last_Name_Other;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $suffix;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'enum', length: 16, nullable: true)]
    private ?string $gender;

    /**
     * @var array|string[]
     */
    private array $genderList =
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
    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    private ?string $birth_Location;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    private ?string $death_Location;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $photo;

    /**
     * @var Individual|null
     */
    #[ORM\OneToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'father')]
    private ?Individual $father;

    /**
     * @var Individual|null
     */
    #[ORM\OneToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'mother')]
    private ?Individual $mother;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer', options: ['unsigned'])]
    private bool $page_ID;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Individual::class, inversedBy: 'profiles')]
    #[ORM\JoinTable(name: 'profiles_managers')]
    #[ORM\JoinColumn(referencedColumnName: 'id', name: 'manager')]
    #[ORM\InverseJoinColumn(name: 'profile', referencedColumnName: 'id')]
    private Collection $managers;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Individual::class, mappedBy: 'managers')]
    #[ORM\JoinTable(name: 'profiles_managers')]
    #[ORM\JoinColumn(referencedColumnName: 'id', name: 'profile')]
    #[ORM\InverseJoinColumn(name: 'manager', referencedColumnName: 'id')]
    private Collection $profiles;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_Living', type: 'boolean')]
    private bool $living;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private int $privacy;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
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
     * Constructor
     */
    public function __construct()
    {
        $this->managers = new ArrayCollection();
        $this->profiles = new ArrayCollection();
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
        return $this->source_ID;
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
     * @return string|null
     */
    public function getUserIDDB(): ?string
    {
        return !is_null($this->user_ID_DB) ? $this->user_ID_DB: $this->getUserID();
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
        if ($this->getUserID() === $this->getUserIDDB()) {
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
        $this->gender = in_array($gender, $this->getGenderList(true)) ? $gender : null;
        return $this;
    }

    /**
     * @return array
     */
    public function getGenderList(bool $withNull = false): array
    {
        return $withNull ? array_merge([null], $this->genderList) : $this->genderList;
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
        $this->birth_Location = empty($birth_Location) ? null : $birth_Location;
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
        $this->death_Location = empty($death_Location) ? null : $death_Location;
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
     * @return bool
     */
    public function isPageID(): bool
    {
        return $this->page_ID;
    }

    /**
     * @param bool $page_ID
     * @return Individual
     */
    public function setPageID(bool $page_ID): Individual
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
     * @return string
     */
    public function parseEventDate(?\DateTimeImmutable $date): string
    {
        if (is_null($date)) return '';
        $yearValid = (bool)intval($date->format('G'));
        if (!$yearValid) return '';
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
}
