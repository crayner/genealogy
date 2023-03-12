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


use App\Entity\ProfilesManagers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ProfilesManagersRepository
 * @selectPure App\Repository
 * @author  Craig Rayner <craig@craigrayner.com>
 */
class ProfilesManagersRepository extends ServiceEntityRepository
{
    /**
     * IndividualRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfilesManagers::class);
    }
}