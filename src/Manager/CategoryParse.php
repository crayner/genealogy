<?php
namespace App\Manager;

use App\Entity\Category;
use App\Entity\Cemetery;
use App\Entity\Individual;
use App\Entity\Migrant;
use App\Repository\CategoryRepository;
use App\Repository\IndividualRepository;
use ContainerAyuJU3n\getCache_App_TaggableService;
use Doctrine\Common\Collections\ArrayCollection;
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
    static int $bulk = 100;

    /**
     * @var Individual
     */
    var Individual $individual;

    /**
     * @var ArrayCollection
     */
    var ArrayCollection $individuals;

    /**
     * @var Category|null
     */
    var ?Category $category = null;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->individualRepository = $this->entityManager->getRepository(Individual::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->individuals = new ArrayCollection();
    }

    public function execute(int $offset, string $letter): mixed
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_categories.csv');

        $letterFile = __DIR__ . '/../../../dumps/dump_categories_'.$letter.'.csv';

        if (!file_exists($letterFile)) {
            file_put_contents($letterFile, $this->createLetterFile($fileName, $letter));
        }
        $letterFile = realpath(__DIR__ . '/../../../dumps/dump_categories_'.$letter.'.csv');

        $file = new \SplFileObject($letterFile);
        $file->seek($offset);
        $individuals = [];
        for($i = 0; !$file->eof() && $file->valid(); $i++, $file->next()) {
            $line = explode("\t", trim($file->current()));
            if (count($line) === 2) {
                $name = trim(str_replace('_', ' ', $line[1]));
                $this->createCategory($line, $name);
            } else {
                $i = static::$bulk + 10;
            }
            if (($this->getCategory() !== null && $file->eof()) || ($this->getCategory() !== null && $name !== $this->getCategory()->getName()) || ($this->getCategory() !== null && $i >= static::$bulk)) {
                try {
                    foreach ($this->getIndividuals() as $individual) {
                        $this->getEntityManager()->persist($individual);
                    }
                    $this->getEntityManager()->persist($this->getCategory());
                    $this->getEntityManager()->flush();
                    if ($file->eof()) {
                        return 0;
                    }
                    return $offset;
                } catch (UniqueConstraintViolationException $e) {
                    throw $e;
                } catch (\ErrorException $e) {
                    return $offset;
                }
            }
            $offset++;
        }
        return 0;
    }

    /**
     * @param array $line
     * @return Individual|null
     */
    private function createCategory(array $line, string $name): ?Category
    {
        if ($this->getCategory() !== null && $this->getCategory()->getName() === $name) {
            $category = $this->getCategory();
        } else {
            $category = $this->getCategoryRepository()->findOneByName($name);
        }
        if ($category === $this->getCategory() && $category !== null) {
            return $this->setIndividual($this->getIndividualRepository()->findOneBySourceID($line[0]))
                ->getCategory()
                ->addIndividual($this->getIndividual());
        }

        if (str_contains($name, 'Cemetery') && is_null($category)) {
            $category = $this->getEntityManager()->getRepository(Cemetery::class)->findOneByName($name);
        }
        if (str_contains($name, 'Migrants') && is_null($category)) {
            $category = $this->getEntityManager()->getRepository(Migrant::class)->findOneByName($name);
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

        $individual = $this->getIndividualRepository()->findOneBySourceID($line[0]);

        $category->addIndividual($individual);

        if (is_null($this->getCategory())) {
            $this->setIndividual($individual)
                ->setCategory($category);
        } elseif ($this->getCategory() === $category) {
            $this->setIndividual($individual);
        }
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

    /**
     * @return Individual
     */
    public function getIndividual(): Individual
    {
        return $this->individual ?? new Individual();
    }

    /**
     * @param Individual|null $individual
     * @return CategoryParse
     */
    public function setIndividual(?Individual $individual): CategoryParse
    {
        if (is_null($individual) || !$individual->isValid()) return $this;
        $this->individual = $individual;
        return $this->addIndividual($individual);
    }

    /**
     * @param string $fileName
     * @param string $letterFile
     * @return void
     */
    private function createLetterFile(string $fileName, string $letter)
    {
        $file = new \SplFileObject($fileName);
        $file->seek(0);

        $lines = [];
        for($i = 0; !$file->eof() && $file->valid(); $i++, $file->next()) {
            $line = explode("\t", trim($file->current()));
            if (!array_key_exists(1, $line)) dump($line);
            $name = trim(str_replace('_', ' ', $line[1]));
            if (strtolower($name[0]) === strtolower($letter)) {
                $lines[] = $line;
            }
        }
        usort($lines, function($a, $b) {
            return $a[1] <=> $b[1];
        });

        $content = '';
        foreach($lines as $line) {
            $content .= implode("\t", $line) . "\r\n";
        }

        return $content;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category ?? null;
    }

    /**
     * @param Category $category
     * @return CategoryParse
     */
    public function setCategory(Category $category): CategoryParse
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getIndividuals(): ArrayCollection
    {
        return $this->individuals;
    }

    /**
     * @param Individual $individual
     * @return $this
     */
    public function addIndividual(Individual $individual): CategoryParse
    {
        if ($this->getIndividuals()->contains($individual) || !$this->getIndividual()->isValid()) return $this;
        $this->getIndividuals()->add($individual);
        return $this;
    }
}