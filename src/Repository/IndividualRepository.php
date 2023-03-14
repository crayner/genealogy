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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class IndividualRepository
 * @selectPure App\Repository
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:59
 */
class IndividualRepository extends ServiceEntityRepository
{
    /**
     * IndividualRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Individual::class);
    }

    /**
     * @param string|null $sourceID
     * @param bool $create
     * @return Individual|null
     */
    public function findOneBySourceID(?string $sourceID, bool $create = false): ?Individual
    {
        $individual = parent::findOneBy(['source_ID' => $sourceID]);
        if ($create && is_null($individual)) {
            $individual = new Individual();
            $individual->setSourceID($sourceID);
        }
        return $individual;
    }

    /**
     * @param array $criteria
     * @return array|null
     */
    public function findLike(array $criteria): ?array
    {
        $query = $this->createQueryBuilder('i');
        $where = '';
        foreach ($criteria as $field => $value) {
            $where .= 'i.' . $field . ' LIKE :' . strtolower($field) . ' AND ';
            $query->setParameter(strtolower($field), $value);
        }

        $query->where(substr($where, 0,-5));
        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param string $userID
     * @return Individual|null
     */
    public function findOneByUserID(string $userID): ?Individual
    {
        $individual = $this->findOneBy(['user_ID' => $userID]);
        while (!empty($individual->getUserIDDB(true))) {
            $individual = $this->findOneBy(['user_ID' => $individual->getUserIDDB()]);
        }
        return $individual;
    }
}