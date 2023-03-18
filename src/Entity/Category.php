<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discriminator', type: 'string', length: 32)]
#[ORM\DiscriminatorMap(['cemetery' => Cemetery::class, 'theme' => Theme::class, 'location' => Location::class, 'category' => Category::class])]
#[ORM\Index(columns: ['location'], name: 'location', options: ['nullable'])]
#[ORM\UniqueConstraint(name: 'name', columns: ['name'])]
class Category
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    var int $id;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Individual::class, mappedBy: 'categories')]
    #[ORM\JoinTable(name: 'individual_category')]
    #[ORM\JoinColumn(name: 'individual', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'category', referencedColumnName: 'id')]
    #[ORM\OrderBy(['last_Name_At_Birth' => 'ASC', 'first_Name' => 'ASC'])]
    var Collection $individuals;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var string $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var string $aka;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'parent_category')]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'parent', referencedColumnName: 'id')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    var Collection $parents;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ?string $project;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'wikidata', type: 'string', length: 32, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ?string $wikidata;

    /**
     * @var string
     */
    #[ORM\Column(name: 'show_profiles', type: 'enum', length: 16, nullable: false, options: ['default' => 'Yes', 'collate' => 'utf8mb4_unicode_ci'])]
    var string $showProfiles;

    /**
     * @var array|string[]
     */
    public static array $showProfilesList = [
        'Yes',
        'No',
        'Maybe'
    ];

    /**
     * @var Category|null
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'project_category', referencedColumnName: 'id')]
    var ?Category $projectCategory;

    /**
     * @var DescriptionPage|null
     */
    #[ORM\ManyToOne(targetEntity: DescriptionPage::class)]
    #[ORM\JoinColumn(name: 'description_page', referencedColumnName: 'id')]
    var ?DescriptionPage $descriptionPage;

    /**
     * @var array|ArrayCollection
     */
    #[ORM\Column(name: 'webpages', type: 'json', options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ArrayCollection|array $webpages;
    public function __construct()
    {
        $this->individuals = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->webpages = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getIndividuals(): Collection
    {
        if ($this->individuals instanceof PersistentCollection && !$this->individuals->isInitialized()) {
            $this->individuals->initialize();
        }
        return $this->individuals;
    }

    /**
     * @param Collection $individuals
     * @return Category
     */
    public function setIndividuals(Collection $individuals): Category
    {
        $this->individuals = $individuals;
        return $this;
    }

    /**
     * @param Individual|null $individual
     * @return $this
     */
    public function addIndividual(?Individual $individual): Category
    {
        if (is_null($individual)) return $this;
        if ($this->getIndividuals()->contains($individual)) return $this;
        $this->getIndividuals()->add($individual);
        $individual->addCategory($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getParents(): Collection
    {
        if ($this->parents instanceof PersistentCollection && !$this->parents->isInitialized()) $this->parents->initialize();
        return $this->parents;
    }

    /**
     * @param Collection $parents
     * @return Category
     */
    public function setParents(Collection $parents): Category
    {
        $this->parents = $parents;
        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addParent(Category $category): Category
    {
        if ($this->getParents()->contains($category)) return $this;
        $this->getParents()->add($category);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProject(): ?string
    {
        return $this->project;
    }

    /**
     * @param string|null $project
     * @return Category
     */
    public function setProject(?string $project): Category
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWikidata(): ?string
    {
        return $this->wikidata;
    }

    /**
     * @param string|null $wikidata
     * @return Category
     */
    public function setWikidata(?string $wikidata): Category
    {
        $this->wikidata = $wikidata;
        return $this;
    }

    /**
     * @return string
     */
    public function getShowProfiles(): string
    {
        return $this->showProfiles;
    }

    /**
     * @param string $showProfiles
     * @return Category
     */
    public function setShowProfiles(string $showProfiles): Category
    {
        $this->showProfiles = in_array($showProfiles, static::getShowProfilesList()) ? $showProfiles : 'Yes';
        return $this;
    }

    /**
     * @return array
     */
    public static function getShowProfilesList(): array
    {
        return self::$showProfilesList;
    }

    /**
     * @return Category|null
     */
    public function getProjectCategory(): ?Category
    {
        return $this->projectCategory;
    }

    /**
     * @param Category|null $projectCategory
     * @return Category
     */
    public function setProjectCategory(?Category $projectCategory): Category
    {
        $this->projectCategory = $projectCategory;
        return $this;
    }

    /**
     * @return DescriptionPage|null
     */
    public function getDescriptionPage(): ?DescriptionPage
    {
        return $this->descriptionPage;
    }

    /**
     * @param DescriptionPage|null $descriptionPage
     * @return Category
     */
    public function setDescriptionPage(?DescriptionPage $descriptionPage): Category
    {
        $this->descriptionPage = $descriptionPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getAka(): string
    {
        return $this->aka;
    }

    /**
     * @param string $aka
     * @return Category
     */
    public function setAka(string $aka): Category
    {
        $this->aka = $aka;
        return $this;
    }

    /**
     * @param bool $array
     * @return ArrayCollection|array
     */
    public function getWebpages(bool $array = true): ArrayCollection|array
    {
        if ($array) $this->webpages->toArray();
        return $this->webpages;
    }

    /**
     * @param ArrayCollection|array $webpages
     * @return Category
     */
    public function setWebpages(ArrayCollection|array $webpages): Category
    {
        $this->webpages = is_array($webpages) ? new ArrayCollection($webpages) :$webpages;
        return $this;
    }
}