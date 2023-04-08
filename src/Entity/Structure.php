<?php
namespace App\Entity;

use App\Repository\StructureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Structure extends Location
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
     * @var string
     */
    #[ORM\Column(name: 'structure_type', type: 'enum', length: 32, nullable: false, options: ['default' => 'unknown'])]
    var string $structureType = 'unknown';

    static public array $structureTypeList = [
        'unknown' => 'Unknown',
        'hospital' => 'Hospital',
        'school' => 'School',
        'religious' => 'Religious',
    ];


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
    public function setLocation(?Location $location): Structure
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
    public function setAddress(?string $address): Structure
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return Cemetery
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function addLocationToParent(): Structure
    {
        if (is_null($this->getLocation())) return $this;
        $this->addParent($this->getLocation());
        return $this;
    }

    /**
     * @return string
     */
    public function getStructureType(): string
    {
        return $this->structureType;
    }

    /**
     * @param string $structureType
     * @return Structure
     */
    public function setStructureType(string $structureType): Structure
    {
        $this->structureType = $structureType;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStructureTypeList(): array
    {
        return self::$structureTypeList;
    }
}