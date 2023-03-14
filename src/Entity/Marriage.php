<?php

namespace App\Entity;

use App\Repository\MarriageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Individual
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @selectPure App\Entity
 */
#[ORM\Entity(repositoryClass: MarriageRepository::class)]
#[ORM\Table(name: 'marriage', options: ['collate' => 'utf8mb4_unicode_ci'])]
class Marriage
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    private int $id;

    /**
     * @var Individual
     */
    #[ORM\ManyToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'husband', referencedColumnName: 'id')]
    private Individual $husband;

    /**
     * @var Individual
     */
    #[ORM\ManyToOne(targetEntity: Individual::class)]
    #[ORM\JoinColumn(name: 'wife', referencedColumnName: 'id')]
    private Individual $wife;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(name: 'marriage_date', type: 'datetime_immutable', nullable: true, options: ['comment' => '(DC2Type:datetime_immutable)'])]
    private ?\DateTimeImmutable $marriageDate;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'marriage_date_status', type: 'enum', nullable: true)]
    private ?string $marriageDateStatus;

    /**
     * @var array|string[]
     */
    static public array $marriageDateStatusList = [
        'about',
        'certain',
        'before',
        'after'
    ];

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(name: 'marriage_end_date', type: 'datetime_immutable', nullable: true, options: ['comment' => '(DC2Type:datetime_immutable)'])]
    private ?\DateTimeImmutable $marriageEndDate;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $location;

    #[ORM\Column(name: 'location_status', type: 'enum', nullable: true)]
    private ?string $locationStatus;

    /**
     * @var array|string[]
     */
    public static array $locationStatusList = [
        'certain',
        'uncertain',
    ];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Individual
     */
    public function getHusband(): Individual
    {
        return $this->husband;
    }

    /**
     * @param Individual $husband
     * @return Marriage
     */
    public function setHusband(Individual $husband): Marriage
    {
        $this->husband = $husband;
        return $this;
    }

    /**
     * @return Individual
     */
    public function getWife(): Individual
    {
        return $this->wife;
    }

    /**
     * @param Individual $wife
     * @return Marriage
     */
    public function setWife(Individual $wife): Marriage
    {
        $this->wife = $wife;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getMarriageDate(): ?\DateTimeImmutable
    {
        return $this->marriageDate;
    }

    /**
     * @param \DateTimeImmutable|null $marriageDate
     * @return Marriage
     */
    public function setMarriageDate(?\DateTimeImmutable $marriageDate): Marriage
    {
        $this->marriageDate = $marriageDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMarriageDateStatus(): ?string
    {
        return $this->marriageDateStatus;
    }

    /**
     * @param string|null $marriageDateStatus
     * @return $this
     */
    public function setMarriageDateStatus(?string $marriageDateStatus): Marriage
    {
        $this->marriageDateStatus = $marriageDateStatus;
        return $this;
    }

    /**
     * @return array
     */
    public static function getMarriageDateStatusList(): array
    {
        return self::$marriageDateStatusList;
    }


    /**
     * @return \DateTimeImmutable|null
     */
    public function getMarriageEndDate(): ?\DateTimeImmutable
    {
        return $this->marriageEndDate;
    }

    /**
     * @param \DateTimeImmutable|null $marriageEndDate
     * @return Marriage
     */
    public function setMarriageEndDate(?\DateTimeImmutable $marriageEndDate): Marriage
    {
        $this->marriageEndDate = $marriageEndDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     * @return Marriage
     */
    public function setLocation(?string $location): Marriage
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocationStatus(): ?string
    {
        return $this->locationStatus;
    }

    /**
     * @param string|null $locationStatus
     * @return Marriage
     */
    public function setLocationStatus(?string $locationStatus): Marriage
    {
        $this->locationStatus = $locationStatus;
        return $this;
    }

    /**
     * @return array
     */
    public static function getLocationStatusList(): array
    {
        return self::$locationStatusList;
    }

}