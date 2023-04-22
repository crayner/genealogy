<?php
namespace App\Entity;

use App\Form\Validation\WebPagesConstraint;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discriminator', type: 'string', length: 32)]
#[ORM\DiscriminatorMap([
    'category' => Category::class,
    'cemetery' => Cemetery::class,
    'collection' => \App\Entity\Collection::class,
    'location' => Location::class,
    'migrant' => Migrant::class,
    'structure' => Structure::class,
    'theme' => Theme::class])]
#[ORM\UniqueConstraint(name: 'category_name', columns: ['name'])]
#[ORM\HasLifecycleCallbacks]
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
    #[MaxDepth(2)]
    var Collection $individuals;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false, options: ['collate' => 'utf8mb4_unicode_ci'])]
    #[Assert\NotBlank(message: 'The category name should not be blank.')]
    var string $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'display_name', type: 'string', length: 255, unique: false, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ?string $displayName = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sort_name', type: 'string', length: 255, unique: false, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ?string $sortName = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['collate' => 'utf8mb4_unicode_ci'])]
    var ?string $aka = null;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'parent_category')]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'parent', referencedColumnName: 'id')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[MaxDepth(2)]
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
    #[ORM\Column(name: 'show_profiles', type: 'string', length: 16, nullable: false, options: ['default' => 'Yes', 'collate' => 'utf8mb4_unicode_ci'])]
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
     * @var Collection
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: CategoryWebPage::class, cascade: ['persist'])]
    #[ORM\OrderBy(['prompt' => 'ASC', 'name' => 'ASC'])]
    #[WebPagesConstraint]
    var Collection $webpages;

    /**
     * @var array|string[]
     */
    static public array $categoryTypeList = [
        'category',
        'cemetery',
        'collection',
        'location',
        'migrant',
        'theme',
        'structure'
    ];

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

        if (!$individual->isValid()) return $this;

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
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return is_null($this->displayName) ? $this->getName() : $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return Category
     */
    public function setDisplayName(?string $displayName): Category
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSortName(): ?string
    {
        return is_null($this->sortName) ? $this->getName() : $this->sortName;
    }

    /**
     * @param string|null $sortName
     * @return Category
     */
    public function setSortName(?string $sortName): Category
    {
        $this->sortName = $sortName;
        return $this;
    }

    /**
     * @return Category
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function clearNames(): Category
    {
        if ($this->sortName === $this->name || empty($this->sortName)) $this->setSortName(null);
        if ($this->displayName === $this->name || empty($this->displayName)) $this->setDisplayName(null);

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
     * @return bool
     */
    public function addParent(Category $category): bool
    {
        if ($this->getParents()->contains($category)) return false;
        if ($this->isEqualTo($category)) return false;
        $this->getParents()->add($category);
        return true;
    }

    /**
     * @param Category $category
     * @return bool
     */
    public function removeParent(Category $category): bool
    {
        if (!$this->getParents()->contains($category)) return false;
        $this->getParents()->removeElement($category);
        return true;
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
     * @return string|null
     */
    public function getAka(): ?string
    {
        return $this->aka ?? null;
    }

    /**
     * @param string|null $aka
     * @return $this
     */
    public function setAka(?string $aka): Category
    {
        $this->aka = $aka;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getWebpages(): Collection
    {
        if ($this->webpages instanceof PersistentCollection && !$this->webpages->isInitialized()) $this->webpages->initialize();

        return $this->webpages;
    }

    /**
     * @param Collection $webpages
     * @return Category
     */
    public function setWebpages(Collection $webpages): Category
    {
        $this->webpages = $webpages;
        return $this;
    }

    /**
     * @param CategoryWebPage $page
     * @return Category
     */
    public function addWebpage(CategoryWebPage $page): Category
    {
        if ($this->getWebpages()->containsKey($page->getName(true)) || $this->getWebpages()->contains($page)) return $this;
        $page->setCategory($this);
        $this->getWebpages()->add($page);
        return $this;
    }

    /**
     * @param CategoryWebPage $page
     * @return Category
     */
    public function removeWebPage(CategoryWebPage $page): Category
    {
        if ($this->getWebpages()->contains($page)) $this->getWebpages()->removeElement($page);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    /**
     * @return string
     */
    public function getCategoryType(): string
    {
        if (isset($this->categoryType)) return $this->categoryType;
        switch (get_class($this)) {
            case Location::class:
                $this->categoryType = 'location';
                break;
            case Cemetery::class:
                $this->categoryType = 'cemetery';
                break;
            case Category::class:
                $this->categoryType = 'category';
                break;
            default:
                dump(get_class($this));
                $this->categoryType = 'category';
        }
        return $this->categoryType;
    }

    /**
     * @param bool $choice
     * @return array|string[]
     */
    public static function getCategoryTypeList(bool $choice = false): array
    {
        if ($choice) {
            $choices = [];
            foreach (self::$categoryTypeList as $choice)
                $choices[ucfirst($choice)] = $choice;
            return $choices;
        }
        return self::$categoryTypeList;
    }

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return null;
    }

    /**
     * @param Location|null $location
     * @return Category
     */
    public function setLocation(?Location $location): Category
    {
        $this->location = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getName();
    }

    /**
     * @param Category $category
     * @return bool
     */
    public function isEqualTo(Category $category): bool
    {
        if ($category->getId() === $this->getId()) return true;
        if ($category->getName() === $this->getName()) return true;
        return false;
    }

    /**
     * @param array $webpage
     * @return array
     */
    static public function testWebpageDefinition(array $webpage): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'prompt',
                'url',
                'name',
            ]
        );
        $resolver->setDefaults(
            [
                'key' => null,
                'test' => null,
            ]
        );
        $resolver->setAllowedTypes('prompt', 'string');
        return $resolver->resolve($webpage);
    }

    public function getExistingWebpage(mixed $value): CategoryWebPage|bool
    {
        foreach ($this->getWebpages() as $webpage) {
            if ($value === $webpage->getName()) return $webpage;
        }
        return false;
    }

    public function getGoogleMapType(): string
    {
        return 'normal';
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}