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
 * Date: 1/04/2021
 * Time: 10:29
 */

namespace App\Manager;

use App\Entity\Individual;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class FamilyHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 10:29
 */
class FamilyHandler
{
    /**
     * @var EventHandler
     */
    private EventHandler $eventHandler;

    /**
     * @var MultimediaRecordHandler
     */
    private MultimediaRecordHandler $multimediaRecordHandler;

    /**
     * FamilyHandler constructor.
     * @param EventHandler $eventHandler
     * @param MultimediaRecordHandler $multimediaRecordHandler
     */
    public function __construct(EventHandler $eventHandler, MultimediaRecordHandler $multimediaRecordHandler)
    {
        $this->eventHandler = $eventHandler;
        $this->multimediaRecordHandler = $multimediaRecordHandler;
    }

    /**
     * @param ArrayCollection $familyDetails
     * @param bool $key
     */
    public function parse(ArrayCollection $familyDetails)
    {
        $line = LineManager::getLineDetails($familyDetails->get(0));
        extract($line);
        $identifier = trim($tag, 'F@');
        $family = GedFileHandler::getFamily($identifier);

        $q = 1;
        while ($familyDetails->containsKey($q)) {
            extract(LineManager::getLineDetails($familyDetails->get($q)));
            switch ($tag) {
                case 'HUSB':
                    $identifier = trim($content, "PI@");
                    $individual = GedFileHandler::getIndividual($identifier);
                    $indfam = GedFileHandler::getIndividualFamily($individual,$family,'Husband');
                    $indfam->setRelationshipType('Husband');
                    break;
                case 'WIFE':
                    $identifier = trim($content, "PI@");
                    $individual = GedFileHandler::getIndividual($identifier);
                    $indfam = GedFileHandler::getIndividualFamily($individual,$family,'Wife');
                    $indfam->setRelationshipType('Wife');
                    break;
                case 'CHIL':
                    $identifier = trim($content, "PI@");
                    $individual = GedFileHandler::getIndividual($identifier);
                    $indfam = GedFileHandler::getIndividualFamily($individual,$family,'Child');
                    $indfam->setRelationshipType('Child');
                    break;
                case 'RIN':
                    $family->setRecordKey($content);
                    break;
                case '_UID':
                case '_UPD':
                case '_FREL':
                case '_MREL':
                case 'UID':
                    $family->addExtra($tag,$content);
                    break;
                case 'EVEN':
                case 'MARR':
                case 'DIV':
                case 'ENGA':
                    $event = ItemHandler::getSubItem($q, $familyDetails);
                    $q += $event->count() - 1;
                    $event = $this->getEventHandler()->parse($event);
                    $family->addEvent($event);
                    break;
                case 'NOTE':
                    $family->setNote($content);
                    break;
                case 'CONC':
                case 'CONT':
                    $family->concatNote($content);
                    break;
                case 'OBJE':
                    $object = ItemHandler::getSubItem($q, $familyDetails);
                    $q += $object->count() - 1;
                    $record = $this->getMultimediaRecordHandler()->parse($object);
                    $family->addMultimediaRecord($record);
                    break;
                default:
                    dump(sprintf('Handling a (%s) is beyond (%s)?', $tag, __CLASS__));
                    dd($familyDetails, $family);

            }
            $q++;
        }

        return $family;
    }

    /**
     * @return EventHandler
     */
    public function getEventHandler(): EventHandler
    {
        return $this->eventHandler;
    }

    /**
     * @return MultimediaRecordHandler
     */
    public function getMultimediaRecordHandler(): MultimediaRecordHandler
    {
        return $this->multimediaRecordHandler;
    }
}
