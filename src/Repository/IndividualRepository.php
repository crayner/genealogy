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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\TextType;
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
        if ($individual === null) return null;
        while (!empty($individual->getUserIDDB(true))) {
            $individual = $this->findOneBy(['user_ID' => $individual->getUserIDDB()]);
        }
        return $individual;
    }

    /**
     * @param $id
     * @param $lockMode
     * @param $lockVersion
     * @return Individual|null
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Individual
    {
        $name = $id;
        $id = intval($name);
        $result = $this->createQueryBuilder('i')
            ->where('i.id = :id OR i.user_ID = :name')
            ->setParameter('id', $id)
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
        return $result[0] ?? null;
    }

    /**
     * @param array $content
     * @return array
     */
    public function quickNameSearch(array $content): ?ArrayCollection
    {
        $rawSearch = trim(str_replace(' ', '', $content['familyName'].$content['givenNames']));
        if (strlen($rawSearch) < 3) return null;
        $search = explode(' ', $content['familyName']);
        $gn = explode(' ', $content['givenNames']);
        $search = array_merge($search, $gn);
        $gn = $gn[0];
        $query = $this->createQueryBuilder('i')
            ->select(['i.id AS value', "CONCAT(i.last_name_at_birth, ' ', i.first_name, ' ', COALESCE(i.middle_name, ''), ' ', COALESCE(i.nick_names, ''), ' ', COALESCE(i.last_name_current, '')) AS label"]);
        if (empty($gn)) {
            $query
                ->where('i.name_index LIKE :familyStart')
                ->setParameter('familyStart', '% ' . strtolower($content['familyName']) . '%');

        } elseif (empty($content['familyName'])) {
            $query
                ->where('i.name_index LIKE :givenStart')
                ->setParameter('givenStart', strtolower($gn) . '%');
        } else {
            $query
                ->where('i.name_index LIKE :familyStart')
                ->andWhere('i.name_index LIKE :givenStart')
                ->setParameter('givenStart', strtolower($gn) . '%')
                ->setParameter('familyStart', '% ' . strtolower($content['familyName']) . '%');
        }
        foreach ($search as $q=>$w) {
            $param =  'any_'.$q;
            if (!empty($w)) {
                $query->andWhere('i.name_index LIKE :' . $param)
                    ->setParameter($param, '%' . strtolower($w) . '%');
            }
        }
        $query
            ->setMaxResults(1000)
            ->orderBy('i.last_name_at_birth', 'ASC')
            ->addOrderBy('i.first_name', 'ASC');
        $result = $query->getQuery()
            ->getResult();
dump($result);
        if (is_array($result) && count($result) > 0) return new ArrayCollection($result);
        return null;
    }
}