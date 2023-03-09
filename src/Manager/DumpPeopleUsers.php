<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Repository\IndividualRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

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
        $bulk = 1147;

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
            $lines[$line[0]]['individual'] = $this->createIndividual($line);
            $this->getEntityManager()->persist($this->createIndividual($line));
            if (++$count > 31) {
                try {
                    $this->getEntityManager()->flush();
                } catch ( UniqueConstraintViolationException $e) {
                    dump($lines);
                    throw $e;
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
    private function createIndividual(array $line): Individual
    {
        $individual = $this->getIndividualRepository()->findOneBySourceID($line[0], true);

        $father = $this->getIndividualRepository()->findOneBySourceID($line[20]);
        $mother = $this->getIndividualRepository()->findOneBySourceID($line[21]);
        $manager = $this->getIndividualRepository()->findOneBySourceID($line[26]);
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
            ->setBirthDate(empty($line[16]) ? null : new \DateTimeImmutable($line[16]))
            ->setDeathDate(empty($line[17]) ? null : new \DateTimeImmutable($line[17]))
            ->setBirthLocation($line[18])
            ->setDeathLocation($line[19])
            ->setFather($father)
            ->setMother($mother)
            ->setPhoto($line[22])
            ->setPageID($line[25])
            ->setManager($manager)
            ->setLiving((bool)$line[28])
            ->setPrivacy($line[29])
            ->setBackground($line[30])
            ->setThankCount($line[31])
            ->setLocked((bool)$line[32])
            ->setGuest((bool)$line[33])
            ->setConnected((bool)$line[34])
            ;
    }
}