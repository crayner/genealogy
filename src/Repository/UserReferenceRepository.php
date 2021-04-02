<?php

namespace App\Repository;

use App\Entity\UserReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserReference[]    findAll()
 * @method UserReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserReference::class);
    }

    // /**
    //  * @return UserReference[] Returns an array of UserReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserReference
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
