<?php

namespace App\Repository;

use App\Entity\Gedcom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gedcom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gedcom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gedcom[]    findAll()
 * @method Gedcom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GedcomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gedcom::class);
    }

    // /**
    //  * @return Gedcom[] Returns an array of Gedcom objects
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
    public function findOneBySomeField($value): ?Gedcom
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
