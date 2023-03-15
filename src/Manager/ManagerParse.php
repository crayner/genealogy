<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\Marriage;
use App\Repository\IndividualRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class ManagerParse
{
    /**
     * @var IndividualRepository
     */
    var IndividualRepository $individualRepository;

    /**
     * @var EntityManagerInterface
     */
    var EntityManagerInterface $entityManager;

    /**
     * @var int
     */
    static int $bulk = 1333;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->individualRepository = $this->entityManager->getRepository(Individual::class);
    }

    public function execute(): mixed
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_managers.csv');

        $offset = array_key_exists('offset', $_GET) ? $_GET['offset'] : 1;
        $count = 0;

        $file = new \SplFileObject($fileName);
        $file->seek($offset);

        $lines = [];
        for($i = 1; $i < static::$bulk && $file->valid(); $i++, $file->next()) {
            $line = explode(",", trim($file->current()));
            $lines[$line[0]]['line'] = $line;
            $lines[$line[0]]['manager'] = $this->createManagerRelationship($line);

            if ($lines[$line[0]]['manager'] !== null) {
                if (++$count > 45) {
                    try {
                        $this->getEntityManager()->flush();
                    } catch (UniqueConstraintViolationException $e) {
                        dump($lines);
                        throw $e;
                    } catch (\ErrorException $e) {
                        return $offset;
                    }
                    $count = 0;
                    $lines = [];
                }
            }
        }
        $this->getEntityManager()->flush();
        if ($file->valid()) {
            return $offset + static::$bulk;
        }
        return 0;
    }

    /**
     * @param array $line
     * @return Individual|null
     */
    private function createManagerRelationship(array $line): ?Individual
    {
        $manager = $this->getIndividualRepository()->findOneBySourceID($line[0]);
        if (is_null($manager)) return null;

        $profile = $this->getIndividualRepository()->findOneBySourceID($line[1]);
        if (is_null($profile)) return null;

        $profile->addManager($manager);
        $this->getEntityManager()->persist($manager);
        $this->getEntityManager()->persist($profile);

        return $profile;
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
}