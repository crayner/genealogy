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
 * Date: 3/04/2021
 * Time: 11:38
 */

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class SourceHandler
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 11:43
 */
class SourceHandler
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * SourceHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArrayCollection $details
     * @param bool $key
     */
    public function parse(ArrayCollection $details)
    {
        $line = LineManager::getLineDetails($details->get(0));
        extract($line);
        $identifier = trim($tag, '@');
        $source = GedFileHandler::getSource($identifier);

        $q = 1;
        while ($details->containsKey($q)) {
            extract(LineManager::getLineDetails($details->get($q)));
            switch ($tag) {
                case 'AUTH':
                    $source->setAuthority($content);
                    break;
                case 'TITL':
                    $source->setTitle($content);
                    break;
                case 'TEXT':
                    $source->setSourceText($content);
                    break;
                case 'RIN':
                    $source->setRecordKey($content);
                    break;
                case 'NOTE':
                    $source->setNote($content);
                    break;
                case 'PUBL':
                    $source->setPublish($content);
                    if ($details->containsKey($q+1)) {
                        extract(LineManager::getLineDetails($details->get($q + 1)), EXTR_PREFIX_ALL, 'publ');
                        if ($publ_tag === 'CONC' && $publ_level > $level) {
                            $q++;
                            $source->setPublish($content.$publ_content);
                        }
                    }
                    break;
                case '_TYPE':
                case '_MEDI':
                case '_APID':
                    $source->addExtra($tag,$content);
                    break;
                case 'REPO':
                    $identifier = trim($content, '@');
                    $repository = GedFileHandler::getRepository($identifier);
                    $source->setRepositoryRecord($repository);
                    $data = ItemHandler::getSubItem($q, $details);  // handle deprecated formats and IGNORE.
                    $q += $data->count() - 1;
                    break;
                default:
                    dump(sprintf('Handling a (%s) is beyond %s!', $tag, __CLASS__));
                    dd($details, $source);

            }
            $q++;
        }

        $this->getEntityManager()->persist($source);
        return $source;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}