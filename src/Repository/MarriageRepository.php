<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: Craig Rayner
 * Date: 30/03/2021
 * Time: 08:58
 */

namespace App\Repository;

use App\Entity\Individual;
use App\Entity\Marriage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class MarriageRepository
 * @selectPure App\Repository
 * @author  Craig Rayner <craig@craigrayner.com>
 */
class MarriageRepository extends ServiceEntityRepository
{
    /**
     * MarriageRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marriage::class);
    }

    /**
     * @param Individual $husband
     * @param Individual $wife
     * @param bool $create
     * @return Individual|null
     */
    public function findOneByMarriage(Individual $husband, Individual $wife, bool $create = false): ?Marriage
    {
        $marriage = parent::findOneBy(['husband' => $husband, 'wife' => $wife]);
        if (is_null($marriage)) $marriage = parent::findOneBy(['husband' => $wife, 'wife' => $husband]);

        if ($create && is_null($marriage)) {
            $marriage = new Marriage();
            if ($husband->getGender() === 'Male') {
                $marriage->setHusband($husband)
                    ->setWife($wife);
            } else {
                $marriage->setHusband($wife)
                    ->setWife($husband);
            }
        }
        return $marriage;
    }

    /**
     * @param Individual $individual
     * @return array|null
     */
    public function findBySpouse(Individual $individual): ?array
    {
        return $this->createQueryBuilder('m')
            ->where("m.husband = ". $individual->getId(). ' OR m.wife = ' . $individual->getId())
            ->orderBy('m.marriageDate')
            ->getQuery()
            ->getResult();
    }
}