<?php
namespace App\Entity;

use App\Entity\Enum\CemeteryWebPageEnum;
use App\Entity\Enum\LocationWebPageEnum;
use App\Repository\CategoryWebPagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryWebPagesRepository::class)]
#[ORM\Table(name: 'category_web_pages', options: ['collate' => 'utf8mb4_unicode_ci'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'category_web_page', columns: ['category', 'name'])]
#[ORM\UniqueConstraint(name: 'category_defined_type', columns: ['category', 'defined_type'])]
#[ORM\Index(columns: ['category'], name: 'category_in_web_pages')]
class CategoryWebPage
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    var int $id;

    /**
     * @var Category
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'webpages')]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'id', nullable: false)]
    var Category $category;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    var string $name;

    /**
     * @var CemeteryWebPageEnum|null
     */
    #[ORM\Column(name: 'defined_type', type: 'string', nullable: true, enumType: CemeteryWebPageEnum::class)]
    var ?CemeteryWebPageEnum $definedType;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    var ?string $prompt;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    var ?string $url;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    var ?string $key;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CategoryWebPage
     */
    public function setId(int $id): CategoryWebPage
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return CategoryWebPage
     */
    public function setCategory(Category $category): CategoryWebPage
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(bool $snakeCase = false): string
    {
        if ($snakeCase) return str_replace(' ', '_', $this->name);
        return $this->name;
    }

    /**
     * @param string $name
     * @return CategoryWebPage
     */
    public function setName(string $name): CategoryWebPage
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return CemeteryWebPageEnum|null
     */
    public function getDefinedType(): ?CemeteryWebPageEnum
    {
        return $this->definedType;
    }

    /**
     * @param CemeteryWebPageEnum|null $definedType
     * @return CategoryWebPage
     */
    public function setDefinedType(?CemeteryWebPageEnum $definedType): CategoryWebPage
    {
        $this->definedType = $definedType ? $definedType : null;
        if ($this->definedType !== null) return $this->buildWebpage();
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrompt(): ?string
    {
        $this->prompt = empty($this->prompt) ? null : $this->prompt;
        if (empty($this->prompt) && !empty($this->key)) return $this->name  . ' - (' . $this->key .')';
        return empty($this->prompt) && empty($this->key) ? $this->name : null;
    }

    /**
     * @param string $prompt
     * @return CategoryWebPage
     */
    public function setPrompt(string $prompt): CategoryWebPage
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return CategoryWebPage
     */
    public function setUrl(string $url): CategoryWebPage
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return CategoryWebPage
     */
    public function setKey(string $key): CategoryWebPage
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return CategoryWebPage
     */
    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function buildWebpage(): CategoryWebPage
    {
        if ($this->getDefinedType() !== null && !$this->getDefinedType()->isNotUsed()) {
            $definition = $this->getDefinedType()->getDefinition();
            $this->setName($definition['name'])
                ->setUrl(str_replace($definition['prompt'], $this->getKey(), $definition['url']));
        }
        if (empty($this->prompt) || $this->prompt === $this->name || $this->prompt === $this->name . ' - (' . $this->key . ')') {
            $this->prompt = null;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getDefinedWebpages(): array
    {
       return match (basename(get_class($this->getCategory()))) {
           'Cemetery' => CemeteryWebPageEnum::cases(),
           'Location' => LocationWebPageEnum::cases(),
        };
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'id' => $this->getId(),
            'category' => $this->getCategory()->getId(),
            'name' => $this->getName(),
            'prompt' => $this->getPrompt(),
            'url' => $this->getUrl(),
            'key' => $this->getKey(),
            'definedType' => $this->getDefinedType(),
        ];
    }
}