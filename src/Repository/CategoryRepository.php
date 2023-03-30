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

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ParseParentRepository
 * @selectPure App\Repository
 * @author  Craig Rayner <craig@craigrayner.com>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param $id
     * @param $lockMode
     * @param $lockVersion
     * @return Category|null
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Category
    {
        $name = $id;
        $id = intval($name);
        $result = $this->createQueryBuilder('c')
            ->where('c.id = :id OR c.name = :name')
            ->setParameter('id', $id)
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
        return $result[0] ?? null;
    }

    /**
     * @param string $search
     * @return array
     */
    public function findBySearch(string $search): array
    {
        $search = explode(' ', $search);
        $where = '';
        $parameters = [];
        foreach ($search as $q=>$w) {
            $where .= ' AND LOWER(c.name) LIKE :name'.$q;
            $parameters['name'.$q] = $w;
        }
        $where = '(' . substr($where, 5) . ')';
        $query = $this->createQueryBuilder('c')
            ->where($where)
            ->setMaxResults(10000)
            ->select(['c.name AS label','c.id AS value'])
            ->orderBy('c.name', 'ASC')
        ;
        foreach ($parameters as $q => $w) {
            $query->setParameter($q, strtolower("%".$w."%"));
        }
        return $query
            ->getQuery()
            ->getResult();
    }
}