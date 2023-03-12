<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Repository\IndividualRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Dump People Users
 */
class DumpPeopleUsers
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
     * @param IndividualRepository $individualRepository
     */
    public function __construct(IndividualRepository $individualRepository, EntityManagerInterface $entityManager)
    {
        $this->individualRepository = $individualRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return mixed
     */
    public function execute(): mixed
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_people_users.csv');

        $offset = array_key_exists('offset', $_GET) ? $_GET['offset'] : 1;
        $count = 0;
        $bulk = 1333;

        $file = new \SplFileObject($fileName);
        if ($offset === 'debug') {
            $line = explode("\t", trim($file->current()));
            dump($line);
            $file->next();
            $file->current();
            $file->next();
            $line = explode("\t", trim($file->current()));
            $individual = $this->createIndividual($line);
            $this->getEntityManager()->persist($individual);
            dump($individual);
            $this->getEntityManager()->flush();
            dump($individual);
            return $line;
        }
        $file->seek($offset);

        $lines = [];
        for($i = 1; $i < $bulk and $file->valid(); $i++, $file->next()) {
            $line = explode("\t", trim($file->current()));
            $lines[$line[0]]['line'] = $line;
            $lines[$line[0]]['individual'] = $this->createIndividual($line, $lines);
            $this->persistIndividual($lines[$line[0]]['individual']);
            if (++$count > 45) {
                try {
                    $this->getEntityManager()->flush();
                } catch ( UniqueConstraintViolationException $e) {
                    dump($lines);
                    throw $e;
                } catch ( \ErrorException $e) {
                    return $offset;
                }
                $count = 0;
                $lines = [];
            }
        }
        $this->getEntityManager()->flush();
        if ($file->valid()) return $offset + $bulk;
        return 0;
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
     * @param array $line
     * @return Individual
     */
    private function createIndividual(array $line, array $lines): Individual
    {
        $individual = $this->getIndividualRepository()->findOneBySourceID($line[0], true);

        $father = $this->getIndividualRepository()->findOneBySourceID($line[20]);
        if (is_null($father) && array_key_exists($line[20], $lines)) {
            $father = $lines[$line[20]]['individual'];
        }
        if ($line[20] > 0 && is_null($father)) {

        }
        $mother = $this->getIndividualRepository()->findOneBySourceID($line[21]);
        if (is_null($mother) && array_key_exists($line[21], $lines)) {
            $mother = $lines[$line[21]]['individual'];
        }
        $manager = $this->getIndividualRepository()->findOneBySourceID($line[26]);
        if (is_null($manager) && array_key_exists($line[26], $lines)) {
            if ($line[26] === $line[0]) {
                $manager = $individual;
            } else {
                $manager = $lines[$line[26]]['individual'];
            }
        }
        return $individual->setSourceID($line[0])
            ->setUserID($line[1])
            ->setUserIDDB($line[2])
            ->setLastTouched(new \DateTimeImmutable($line[3], new \DateTimeZone("UTC")))
            ->setCreatedOn(new \DateTimeImmutable($line[4], new \DateTimeZone("UTC")))
            ->setEditCount((int)$line[5])
            ->setPrefix($line[6])
            ->setFirstName($line[7])
            ->setPreferredName($line[8])
            ->setMiddleName($line[9])
            ->setNickNames($line[10])
            ->setLastNameAtBirth($line[11])
            ->setLastNameCurrent($line[12])
            ->setLastNameOther($line[13])
            ->setSuffix($line[14])
            ->setGender($line[15] === "" ? null : $individual->getGenderList()[intval($line[15])])
            ->setBirthDate($individual->createEventDate($line[16]))
            ->setDeathDate($individual->createEventDate($line[17]))
            ->setBirthLocation($line[18])
            ->setDeathLocation($line[19])
            ->setFather($father)
            ->setMother($mother)
            ->setPhoto($line[22])
            ->setPageID($line[25])
            ->addManager($manager)
            ->setLiving((bool)$line[28])
            ->setPrivacy($line[29])
            ->setBackground($line[30])
            ->setThankCount($line[31])
            ->setLocked((bool)$line[32])
            ->setGuest((bool)$line[33])
            ->setConnected((bool)$line[34])
            ;
    }

    /**
     * @param Individual $individual
     * @return DumpPeopleUsers
     */
    private function persistIndividual(Individual $individual): DumpPeopleUsers
    {
        if ($individual->getFather() instanceof Individual) {
            $this->getEntityManager()->persist($individual->getFather());
        }
        if ($individual->getMother() instanceof Individual) {
            $this->getEntityManager()->persist($individual->getMother());
        }
        if ($individual->getManagers()->count() > 0) {
            foreach ($individual->getManagers()->toArray() as $manager) {
                $this->getEntityManager()->persist($manager);
            }
        }
        $this->getEntityManager()->persist($individual);
        return $this;
    }
}