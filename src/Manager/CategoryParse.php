<?php
namespace App\Manager;

use App\Entity\Category;
use App\Entity\Cemetery;
use App\Entity\Individual;
use App\Entity\Migrant;
use App\Repository\CategoryRepository;
use App\Repository\IndividualRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class CategoryParse
{
    /**
     * @var IndividualRepository
     */
    var IndividualRepository $individualRepository;

    /**
     * @var CategoryRepository
     */
    var CategoryRepository $categoryRepository;

    /**
     * @var EntityManagerInterface
     */
    var EntityManagerInterface $entityManager;

    /**
     * @var int
     */
    static int $bulk = 20;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->individualRepository = $this->entityManager->getRepository(Individual::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
    }

    public function execute(): mixed
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_categories.csv');

        $offset = array_key_exists('offset', $_GET) ? $_GET['offset'] : 1;

        $file = new \SplFileObject($fileName);
        $file->seek($offset);

        $lines = [];
        for($i = 1; $i < static::$bulk && $file->valid(); $i++, $file->next()) {
            $line = explode("\t", trim($file->current()));
            $name = trim(str_replace('_', ' ', $line[1]));
            $category = $this->createCategory($line, $lines, $name);
            if ($category !== null) {
                $lines[$name]['line'] = $line;
                $lines[$name]['category'] = $category;
                if (count($lines) > 19) {
                    try {
                        foreach ($lines as $line) {
                            foreach ($line['category']->getIndividuals() as $individual) {
                                $this->getEntityManager()->persist($individual);
                            }
                            $this->getEntityManager()->persist($line['category']);
                        }
                        $this->getEntityManager()->flush();
                    } catch (UniqueConstraintViolationException $e) {
                        throw $e;
                    } catch (\ErrorException $e) {
                        return $offset;
                    }
                    $lines = [];
                }
            }
        }
        try {
            foreach ($lines as $line) {
                foreach ($line['category']->getIndividuals() as $individual) {
                    $this->getEntityManager()->persist($individual);
                }
                $this->getEntityManager()->persist($line['category']);
            }
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw $e;
        } catch (\ErrorException $e) {
            return $offset;
        }
        if ($file->valid()) {
            return $offset + static::$bulk;
        }
        return 0;
    }

    /**
     * @param array $line
     * @return Individual|null
     */
    private function createCategory(array $line, array $lines, string $name): ?Category
    {
        $individual = $this->getIndividualRepository()->findOneBySourceID($line[0]);

        $category = $this->getCategoryRepository()->findOneByName($name);
        if (str_contains($name, 'Cemetery') && is_null($category)) {
            $category = $this->getEntityManager()->getRepository(Cemetery::class)->findOneByName($name);
        }
        if (str_contains($name, 'Migrants') && is_null($category)) {
            $category = $this->getEntityManager()->getRepository(Migrant::class)->findOneByName($name);
        }
        if (is_null($category) && array_key_exists($name, $lines)) {
            $category = $lines[$name]['category'];
        }
        if (is_null($category)) {
            if (str_contains($name, 'Cemetery')) {
                $category = new Cemetery();
            }
            if (str_contains($name, 'Migrants')) {
                $category = new Migrant();
            }
            $category = is_null($category) ? new Category() : $category;
            $category->setName($name)
                ->setShowProfiles('Yes');
        }

        $category->addIndividual($individual);

        return $category;
    }

    /**
     * @return IndividualRepository
     */
    public function getIndividualRepository(): IndividualRepository
    {
        return $this->individualRepository;
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
}