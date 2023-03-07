<?php

namespace App\Manager;

use App\Entity\Individual;
use App\Repository\IndividualRepository;
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
     * @return void
     */
    public function execute()
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_people_users.csv');

        $headers = [];

        if ($file = fopen($fileName, "r")) {
            while (!feof($file)) {
                $line = explode("\t", trim(fgets($file)));
                if ($headers === []) {
                    $headers = $line;
                    continue;
                }

                $individual = new Individual();

                $individual->setUserIDDB($line[2])
                    ->setUserID($line[1])
                    ->setId($line[0]);
                $this->getEntityManager()->persist($individual);
                dump($individual);
                $this->getEntityManager()->flush();
                dd($headers, $line, $individual);
                break;
            }
        }


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