<?php

namespace App\Manager;

use App\Entity\Category;
use App\Entity\Cemetery;
use App\Entity\Location;
use App\Entity\Migrant;
use App\Entity\Theme;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;

class CategoryManager
{
    /**
     * @var Category|null
     */
    var ?Category $category;

    /**
     * @var EntityManagerInterface
     */
    var EntityManagerInterface $entityManager;

    var CategoryRepository $categoryRepository;

    /**
     * @var IndividualNameManager
     */
    var IndividualNameManager $nameManager;

    /**
     * @var SessionInterface
     */
    var SessionInterface $session;

    /**
     * @var RouterInterface
     */
    var RouterInterface $router;

    /**
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $stack
     * @param SerializerAwareInterface $serialiser
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->session = $stack->getSession();
        $this->router = $router;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category = $this->category ?? null;
    }

    /**
     * @param Category|null $category
     * @return CategoryManager
     */
    public function setCategory(?Category $category): CategoryManager
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param string|null $categoryName
     * @return Category|null
     */
    public function retrieveCategory(?string $categoryName): ?Category
    {
        $this->setCategory($this->getCategoryRepository()->findOneBy(['name' => $categoryName]));
        return $this->getCategory();
    }

    /**
     * @param string|null $categoryName
     * @return Category|null
     */
    public function retrieveCategoryByID(?string $categoryName): ?Category
    {
        $this->setCategory($this->getCategoryRepository()->find($categoryName));
        return $this->getCategory();
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return CategoryRepository
     */
    public function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }

    /**
     * @return IndividualNameManager
     */
    public function getNameManager(): IndividualNameManager
    {
        return $this->nameManager = $this->nameManager ?? new IndividualNameManager();
    }

    /**
     * @param Location $location
     * @param Category $category
     * @return void
     */
    public function saveLocation(Location $location, Category $category)
    {
        $category->addParent($location);
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Category|null $category
     * @return CategoryManager
     */
    public function saveCategory(?Category $category = null): CategoryManager
    {
        if (is_null($category)) $this->category = $this->getCategory();
        if (is_null($category)) return $this;
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
        return $this;
    }

    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * @return string
     */
    public function getCategoryProps(): array
    {
        $result = [
            'id' => $this->getCategory()->getId(),
            'name' => $this->getCategory()->getName(),
            'displayName' => $this->getCategory()->getDisplayName(),
            'sortName' => $this->getCategory()->getSortName(),
            'aka' => $this->getCategory()->getAka(),
            'location' => $this->getCategory()->getLocation() instanceof Category ? $this->getCategory()->getLocation()->getDisplayName() : '',
        ];
        foreach ($this->getCategory()->getIndividuals() as $q => $individual) {
            $result['individuals'][] = $individual->toArray();
            $result['individuals'][$q]['path'] = $this->getRouter()->generate('genealogy_record_modify', ['individual' => $individual->getUserID()]);
        }
        foreach ($this->getCategory()->getParents() as $q => $parent) {
            $result['parents'][$q] = $parent->toArray();
            $result['parents'][$q]['path'] = $this->getRouter()->generate('genealogy_category_modify', ['category' => $parent->getId()]);
        }
        $childrenCategories = $this->getCategoryRepository()->findAllByParent($this->getCategory());
        foreach($childrenCategories as $child) {
            $result['childrenCategories'][$q] = $child->toArray();
            $result['childrenCategories'][$q]['path'] = $this->getRouter()->generate('genealogy_category_modify', ['category' => $child->getId()]);
        }
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'parents' => [],
            'individuals' => [],
            'childrenCategories' => [],
        ]);
        $resolver->setRequired([
            'id',
            'name',
            'sortName',
            'aka',
            'displayName',
            'location',
        ]);

        $result = $resolver->resolve($result);
        return $result;
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param string $categoryType
     * @return $this
     * @throws \Doctrine\DBAL\Exception
     */
    public function writeCategoryDiscriminator(string $categoryType): CategoryManager
    {
        if ($this->getCategoryType() === $categoryType || !$this->checkCategoryType($categoryType)) return $this;
        $query = "UPDATE category SET discriminator = :categoryType WHERE id = :categoryId";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $resultSet = $stmt->executeQuery(['categoryType' => $categoryType, 'categoryId' => $this->getCategory()->getId()]);

        $id = $this->getCategory()->getId();
        $this->setCategory(null);
        $this->retrieveCategoryByID($id);

        return $this;
    }

    /**
     * @return string
     */
    protected function getCategoryType(): string
    {
        switch (get_class($this->getCategory())) {
            case (Location::class):
                return 'location';
                break;
            case (Category::class):
                return 'category';
                break;
            case (Cemetery::class):
                return 'cemetery';
                break;
            case (App\Entity\Collection::class):
                return 'collection';
                break;
            case (Migrant::class):
                return 'migrant';
                break;
            case (Theme::class):
                return 'theme';
                break;
            default:
                dd(get_class($this->getCategory()));
        }
    }

    /**
     * @param string $categoryType
     * @return bool
     */
    protected function checkCategoryType(string $categoryType): bool
    {
        return match ($categoryType) {
            'category', 'cemetery', 'collection', 'location', 'migrant', 'theme' => true,
            default => false,
        };
    }
}