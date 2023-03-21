<?php

namespace App\Manager;

use App\Entity\Category;
use App\Entity\Location;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->session = $stack->getSession();
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
     * @param Collection $parents
     * @param Category $category
     * @return void
     */
    public function saveParents(Collection $parents, Category $category)
    {
        foreach ($parents as $parent) {
            $category->addParent($parent);
            $this->getEntityManager()->persist($category);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->session;
    }
}