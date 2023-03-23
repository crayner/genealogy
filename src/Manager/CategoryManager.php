<?php

namespace App\Manager;

use App\Entity\Category;
use App\Entity\Location;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        ];
        foreach ($this->getCategory()->getIndividuals() as $q=>$individual) {
            $result['individuals'][] = $individual->toArray();
            $result['individuals'][$q]['path'] = $this->getRouter()->generate('genealogy_record_modify', ['individual' => $individual->getUserID()]);
        }
        foreach ($this->getCategory()->getParents() as $q=>$parent) {
            $result['parents'][$q] = $parent->toArray();
            $result['parents'][$q]['path'] = $this->getRouter()->generate('genealogy_category_modify', ['category' => $parent->getId()]);
        }
        return $result;
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter(): RouterInterface
    {
        return $this->router;
    }
}