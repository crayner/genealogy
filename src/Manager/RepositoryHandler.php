<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this repository code.
 *
 * User: Craig Rayner
 * Date: 3/04/2021
 * Time: 11:25
 */

namespace App\Manager;

use App\Entity\RepositoryRecord;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RepositoryHandler
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 13:41
 */
class RepositoryHandler
{
    /**
     * @var AddressHandler $addressHandler
     */
    private AddressHandler $addressHandler;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * RepositoryHandler constructor.
     * @param AddressHandler $addressHandler
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(AddressHandler $addressHandler, EntityManagerInterface $entityManager)
    {
        $this->addressHandler = $addressHandler;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArrayCollection $details
     * @return RepositoryRecord
     */
    public function parse(ArrayCollection $details): RepositoryRecord
    {
        $line = LineManager::getLineDetails($details->get(0));
        $content = '';
        $tag = '';
        extract($line);
        $identifier = trim($tag, '@');
        $repository = GedFileHandler::getRepository($identifier);

        $q = 1;
        while ($details->containsKey($q)) {
            extract(LineManager::getLineDetails($details->get($q)));
            switch ($tag) {
                case 'NAME':
                    $repository->setName($content);
                    break;
                case 'ADDR':
                    $address = ItemHandler::getSubItem($q, $details);
                    $q += $address->count() - 1;
                    $address = $this->getAddressHandler()->parse($address);
                    $repository->setAddress($address);
                    break;
                case 'NOTE':
                    $repository->setNote($content);
                    break;
                default:
                    dump(sprintf('Handling a (%s) is beyond %s!', $tag, __CLASS__));
                    dd($details, $repository);

            }
            $q++;
        }

        $this->getEntityManager()->persist($repository);
        return $repository;
    }

    /**
     * @return AddressHandler
     */
    public function getAddressHandler(): AddressHandler
    {
        return $this->addressHandler;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
