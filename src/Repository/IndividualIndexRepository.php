<?php

namespace App\Repository;

use App\Entity\Individual;
use App\Entity\IndividualIndex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IndividualIndexRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndividualIndex::class);
    }

    /**
     * @param Individual $individual
     * @return IndividualIndex|null
     */
    public function findOrCreate(Individual $individual): ?IndividualIndex
    {
        $result = $this->findOneBy(['individual' => $individual->getId()]);
        if (is_null($result)) {
            $result = new IndividualIndex($individual);
        }
        return $result;
    }
}