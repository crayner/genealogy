<?php

namespace App\Repository;

use App\Entity\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AddressRepository
 * @package App\Repository
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 16:32
 */
class AddressRepository extends ServiceEntityRepository
{
    /**
     * AddressRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }
}
