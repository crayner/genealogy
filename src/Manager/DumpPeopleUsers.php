<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\ParseParent;
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
     * @var int
     */
    var int $highestSource = 0;

    /**
     * @var int
     */
    var int $notFound = 0;

    /**
     * @var int
     */
    var int $corrected = 0;

    /**
     * @var int
     */
    static int $bulk = 1333;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->individualRepository = $entityManager->getRepository(Individual::class);
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

        $file = new \SplFileObject($fileName);
        $file->seek($offset);

        $lines = [];
        for($i = 1; $i < static::$bulk && $file->valid(); $i++, $file->next()) {
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
        if ($file->valid()) {
            $this->parseParentsManagers();
            return $offset + static::$bulk;
        }
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
        if ($line[0] === $line[20]) $line[20] = null;
        if (is_null($father) && array_key_exists($line[20], $lines)) {
            $father = $lines[$line[20]]['individual'];
        }
        if ($line[20] > 0 && is_null($father) && $line[20] > $line[0]) {
            $parseFather = new ParseParent($line[0], $line[20], 'Father');
            $this->getEntityManager()->persist($parseFather);
            $this->incNotFound();
        }

        if ($line[0] === $line[21]) $line[21] = null;
        $mother = $this->getIndividualRepository()->findOneBySourceID($line[21]);
        if (is_null($mother) && array_key_exists($line[21], $lines)) {
            $mother = $lines[$line[21]]['individual'];
        }
        if ($line[21] > 0 && is_null($father) && $line[21] > $line[0]) {
            $parseMother = new ParseParent($line[0], $line[21], 'Mother');
            $this->getEntityManager()->persist($parseMother);
            $this->incNotFound();
        }

        $manager = $this->getIndividualRepository()->findOneBySourceID($line[26]);
        if (is_null($manager) && array_key_exists($line[26], $lines)) {
            if ($line[26] === $line[0]) {
                $manager = $individual;
            } else {
                $manager = $lines[$line[26]]['individual'];
            }
        }
        if ($line[26] > 0 && is_null($manager) && $line[26] > $line[0]) {
            $parseManager = new ParseParent($line[0], $line[26], 'Manager');
            $this->getEntityManager()->persist($parseManager);
            $this->incNotFound();
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
        $this->setHighestSource($individual->getSourceID());
        $this->getEntityManager()->persist($individual);
        return $this;
    }

    /**
     * @return DumpPeopleUsers
     */
    private function parseParentsManagers(): DumpPeopleUsers
    {
        $repository = $this->getEntityManager()->getRepository(ParseParent::class);
        $list = $repository->findBy([],['parent' => 'ASC'], 50);
        $count = 0;
        $failures = 0;
        while ($count <= static::$bulk * 3) {
            while (count($list) > 0) {
                $record = array_shift($list);
                $parent = $this->getIndividualRepository()->findOneBySourceID($record->getParent());
                //remove the records for parent/manager that will never exist.
                if (is_null($parent) && $record->getParent() < $this->getHighestSource()) {
                    $this->getEntityManager()->remove($record);
                    ++$failures;
                    continue;
                }
                // If parent/manager not yet available then stop parsing after static::$bulk such failures.
                if (is_null($parent)) {
                    if (++$failures > static::$bulk) {
                        $this->getEntityManager()->flush();
                        return $this;
                    }
                    continue;
                }
                $child = $this->getIndividualRepository()->findOneBySourceID($record->getChild());
                switch ($record->getRelationship()) {
                    case 'Mother':
                        $child->setMother($parent);
                        break;
                    case 'Father':
                        $child->setFather($parent);
                        break;
                    case 'manager':
                        $child->addManager($parent);
                        break;
                }
                $this->incCorrected()->getEntityManager()->persist($parent);
                $this->getEntityManager()->persist($child);
                $this->getEntityManager()->remove($record);
            }
            $count += 50;
            $this->getEntityManager()->flush();
            $list = $repository->findBy([],['parent' => 'ASC'], 50);
            if (count($list) < 50) $count = 1000;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getHighestSource(): int
    {
        return $this->highestSource;
    }

    /**
     * @param int $highestSource
     * @return DumpPeopleUsers
     */
    public function setHighestSource(int $highestSource): DumpPeopleUsers
    {
        $this->highestSource = max($highestSource, $this->highestSource);
        return $this;
    }

    /**
     * @return int
     */
    public function getNotFound(): int
    {
        return $this->notFound;
    }

    /**
     * @return $this
     */
    public function incNotFound(): DumpPeopleUsers
    {
        ++$this->notFound;
        return $this;
    }

    /**
     * @param int $notFound
     * @return DumpPeopleUsers
     */
    public function setNotFound(int $notFound): DumpPeopleUsers
    {
        $this->notFound = $notFound;
        return $this;
    }

    /**
     * @return int
     */
    public function getCorrected(): int
    {
        return $this->corrected;
    }

    /**
     * @return $this
     */
    public function incCorrected(): DumpPeopleUsers
    {
        ++$this->corrected;
        return $this;
    }
}