<?php

namespace App\Manager;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

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
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
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
}