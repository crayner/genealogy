<?php

namespace App\Entity;

use App\Repository\DescriptionPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DescriptionPageRepository::class)]
#[ORM\Table(name: 'description_page', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\UniqueConstraint(name: 'description_name', columns: ['name'])]
class DescriptionPage
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    var int $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    var string $name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    var ?string $description;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DescriptionPage
     */
    public function setId(int $id): DescriptionPage
    {
        $this->id = $id;
        return $this;
    }


}