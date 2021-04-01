<?php

namespace App\Repository;

use App\Entity\IndividualFamily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IndividualFamily|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndividualFamily|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndividualFamily[]    findAll()
 * @method IndividualFamily[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualFamilyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndividualFamily::class);
    }

    // /**
    //  * @return IndividualFamily[] Returns an array of IndividualFamily objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IndividualFamily
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
