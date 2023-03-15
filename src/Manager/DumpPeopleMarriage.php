<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\Marriage;
use App\Entity\ParseParent;
use App\Repository\IndividualRepository;
use App\Repository\MarriageRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class DumpPeopleMarriage
{
    /**
     * @var IndividualRepository
     */
    var IndividualRepository $individualRepository;

    /**
     * @var MarriageRepository
     */
    var MarriageRepository $marriageRepository;

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
        $this->marriageRepository = $this->entityManager->getRepository(Marriage::class);
    }

    public function execute(): mixed
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_people_marriages.csv');

        $offset = array_key_exists('offset', $_GET) ? $_GET['offset'] : 1;
        $count = 0;

        $file = new \SplFileObject($fileName);
        $file->seek($offset);

        $lines = [];
        for($i = 1; $i < static::$bulk && $file->valid(); $i++, $file->next()) {
            $line = explode("\t", trim($file->current()));
            $lines[$line[0]]['line'] = $line;
            $lines[$line[0]]['marriage'] = $this->createMarriage($line);

            if ($lines[$line[0]]['marriage'] !== null) {
                $this->getEntityManager()->persist($lines[$line[0]]['marriage']);
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
     * @return Marriage|null
     */
    private function createMarriage(array $line): ?Marriage
    {
        $husband = $this->getIndividualRepository()->findOneBySourceID($line[0]);
        if (is_null($husband)) return null;

        $wife = $this->getIndividualRepository()->findOneBySourceID($line[1]);
        if (is_null($wife)) return null;

        $marriage = $this->getMarriageRepository()->findOneByMarriage($husband, $wife, true);

        if (array_key_exists(2,$line)) {
            $marriage->setLocation($line[2]);
        }
        if (array_key_exists(3,$line)) {
            $marriage->setMarriageDate($wife->createEventDate($line[3]));
        }
        if (array_key_exists(4,$line)) {
            $status = null;
            switch ($line[4]) {
                case 1:
                    $status = 'about';
                    break;
                case 2:
                    $status = 'certain';
                    break;
                case 8:
                    $status = 'before';
                    break;
                case 9:
                    $status = 'after';
                    break;
                case '':
                    $status = null;
                    break;
                default:
                    dd($line);
            }
            $marriage->setMarriageDateStatus($status);
        }
        if (array_key_exists(5,$line)) {
            $status = null;
            switch ($line[5]) {
                case 1:
                    $status = 'uncertain';
                case 2:
                    $status = 'certain';
                    break;
                case '':
                    $status = null;
                    break;
                default:
                    dd($line);
            }
            if (count($line) > 6) dd($line);
            $marriage->setLocationStatus($status);
        }

        return $marriage;
    }

    /**
     * @return IndividualRepository
     */
    public function getIndividualRepository(): IndividualRepository
    {
        return $this->individualRepository;
    }

    /**
     * @return MarriageRepository
     */
    public function getMarriageRepository(): MarriageRepository
    {
        return $this->marriageRepository;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}