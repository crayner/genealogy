<?php
namespace App\Entity;

use App\Repository\CemeteryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CemeteryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Cemetery extends Location
{
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
     * @var string|null
     */
    #[ORM\Column(name: 'find_a_grave', type: 'string', length: 32, nullable: true)]
    var ?string $findAGrave;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'billion_graves', type: 'string', length: 32, nullable: true)]
    var ?string $billionGraves;

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
     * @return string|null
     */
    public function getFindAGrave(): ?string
    {
        return $this->findAGrave;
    }

    /**
     * @param string|null $findAGrave
     * @return Cemetery
     */
    public function setFindAGrave(?string $findAGrave): Cemetery
    {
        $this->findAGrave = $findAGrave;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBillionGraves(): ?string
    {
        return $this->billionGraves;
    }

    /**
     * @param string|null $billionGraves
     * @return Cemetery
     */
    public function setBillionGraves(?string $billionGraves): Cemetery
    {
        $this->billionGraves = $billionGraves;
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
}