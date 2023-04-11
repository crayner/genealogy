<?php
namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location extends Category
{
    CONST ZOOM_LEVEL = 12;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 48, nullable: true)]
    var ?string $coordinates = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(name: 'start_date', type: 'datetime_immutable', nullable: true)]
    var ?\DateTimeImmutable $startDate;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(name: 'end_date', type: 'datetime_immutable', nullable: true)]
    var ?\DateTimeImmutable $endDate;

    /**
     * @return string|null
     */
    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    /**
     * @param string|null $coordinates
     * @return Location
     */
    public function setCoordinates(?string $coordinates): Location
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeImmutable|null $startDate
     * @return Category
     */
    public function setStartDate(?\DateTimeImmutable $startDate): Category
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeImmutable|null $endDate
     * @return Location
     */
    public function setEndDate(?\DateTimeImmutable $endDate): Location
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getZoomLevel(): int
    {
        return Location::ZOOM_LEVEL;
    }

}