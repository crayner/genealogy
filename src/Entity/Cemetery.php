<?php
namespace App\Entity;

use App\Repository\CemeteryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CemeteryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Cemetery extends Location
{
    CONST ZOOM_LEVEL = 17;

    CONST GOOGLE_MAP_IMAGE = 'satellite';

    /**
     * @var Location|null
     */
    var ?Location $location;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    var ?string $address;

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        $this->location = $this->location ?? null;
        if (is_null($this->location)) {
            foreach ($this->getParents() as $category) {
                if ($category instanceof Location) {
                    $this->setLocation($category);
                    break;
                }
            }
        }
        return $this->location;
    }

    /**
     * @param Location|null $location
     * @return Cemetery
     */
    public function setLocation(?Location $location): Cemetery
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return Cemetery
     */
    public function setAddress(?string $address): Cemetery
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return Cemetery
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function addLocationToParent(): Cemetery
    {
        if (is_null($this->getLocation())) return $this;
        $this->addParent($this->getLocation());
        return $this;
    }

    /**
     * @return int
     */
    public function getZoomLevel(): int
    {
        return Cemetery::ZOOM_LEVEL;
    }

    /**
     * @return string
     */
    public function getGoogleMapType(): string
    {
        return Cemetery::GOOGLE_MAP_IMAGE;
    }


}