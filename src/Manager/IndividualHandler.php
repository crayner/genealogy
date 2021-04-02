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
 * Time: 09:09
 */

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\IndividualName;
use App\Entity\SourceData;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class IndividualHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 09:37
 */
class IndividualHandler
{
    /**
     * @var EventHandler
     */
    private EventHandler $eventHandler;

    /**
     * @var AttributeHandler
     */
    private AttributeHandler $attributeHandler;

    /**
     * @var IndividualNameHandler
     */
    private IndividualNameHandler $individualNameHandler;

    /**
     * @var SourceDataHandler
     */
    private SourceDataHandler $sourceDataHandler;

    /**
     * @var MultimediaRecordHandler
     */
    private MultimediaRecordHandler $multimediaRecordHandler;

    /**
     * IndividualHandler constructor.
     * @param IndividualNameHandler $individualNameHandler
     * @param EventHandler $eventHandler
     * @param AttributeHandler $attributeHandler
     * @param SourceDataHandler $sourceDataHandler
     * @param MultimediaRecordHandler $multimediaRecordHandler
     */
    public function __construct(IndividualNameHandler $individualNameHandler, EventHandler $eventHandler,
                                AttributeHandler $attributeHandler, SourceDataHandler $sourceDataHandler,
                                MultimediaRecordHandler $multimediaRecordHandler)
    {
        $this->individualNameHandler = $individualNameHandler;
        $this->eventHandler = $eventHandler;
        $this->attributeHandler = $attributeHandler;
        $this->sourceDataHandler = $sourceDataHandler;
        $this->multimediaRecordHandler = $multimediaRecordHandler;
    }


    /**
     * @param ArrayCollection $individualDetails
     * @return Individual
     */
    public function parse(ArrayCollection $individualDetails): Individual
    {
        $line = LineManager::getLineDetails($individualDetails->get(0));
        extract($line);
        $identifier = intval(trim($tag, 'IP@'));
        $individual = GedFileHandler::getIndividual($identifier);

        $q = 1;
        while ($q < count($individualDetails)) {
            extract(LineManager::getLineDetails($individualDetails->get($q)));
            switch ($tag) {
                case 'NAME':
                    $name = ItemHandler::getSubItem($q, $individualDetails);
                    $individualName = $this->getIndividualNameHandler()->parse($name, $individual);
                    $individual->addName($individualName);
                    $q += $name->count() - 1;
                    break;
                case 'SEX':
                    $individual->setGender($content);
                    break;
                case 'DEAT':
                case 'BURI':
                case 'EVEN':
                case 'BAPM':
                case 'IMMI':
                case 'OCCU':
                case 'CHR':
                case 'EDUC':
                case 'BIRT':
                    $event = ItemHandler::getSubItem($q, $individualDetails);
                    $q += $event->count() - 1;
                    $event = $this->getEventHandler()->parse($event, 'Individual');
                    $individual->addEvent($event);
                    break;
                case 'RESI':
                    $attribute = ItemHandler::getSubItem($q, $individualDetails);
                    $attribute = $this->getAttributeHandler()->parse($attribute, 'Individual');
                    $q += $attribute->getOffset() - 1;
                    break;
                case 'FAMS':
                    $identifier = intval(trim($content, 'F@'));
                    $family = GedFileHandler::getFamily($identifier);
                    GedFileHandler::addIndividualFamily($individual, $family, 'Spouse');
                    break;
                case 'FAMC':
                    $identifier = intval(trim($content, 'F@'));
                    $family = GedFileHandler::getFamily($identifier);
                    GedFileHandler::addIndividualFamily($individual, $family, 'Child');
                    break;
                case 'SOUR':
                    $sourceData = new SourceData();
                    $individual->addSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $individualDetails);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                case 'RIN':
                    $individual->setRecordKey(intval($content));
                    break;
                case '_UID':  // My Heritage Reference
                case '_UPD':  // My Heritage Stuff
                case 'UID': // Ancestry.com Reference
                    // Ignore rubbish
                    break;
                case 'NOTE':
                    $individual->setNote($content);
                    break;
                case 'CONC':
                case 'CONT':
                    $individual->concatNote($content);
                    break;
                case 'OBJE':
                    $object = ItemHandler::getSubItem($q, $individualDetails);
                    $q += $object->count() - 1;
                    $record = $this->getMultimediaRecordHandler()->parse($object);
                    $individual->addMultimediaRecord($record);
                    break;
                case 'DSCR':
                    $individual->setDescription(explode(',',$content));
                    break;
                default:
                    dump(sprintf('I don\'t know how to handle a "%s" in "%s"', $tag, __CLASS__));
                    dd($individualDetails, $individual);

            }
            $q++;
        }

        return $individual;
    }

    /**
     * @return IndividualNameHandler
     */
    public function getIndividualNameHandler(): IndividualNameHandler
    {
        return $this->individualNameHandler;
    }

    /**
     * @return EventHandler
     */
    public function getEventHandler(): EventHandler
    {
        return $this->eventHandler;
    }

    /**
     * @return AttributeHandler
     */
    public function getAttributeHandler(): AttributeHandler
    {
        return $this->attributeHandler;
    }

    /**
     * @return SourceDataHandler
     */
    public function getSourceDataHandler(): SourceDataHandler
    {
        return $this->sourceDataHandler;
    }

    /**
     * @return MultimediaRecordHandler
     */
    public function getMultimediaRecordHandler(): MultimediaRecordHandler
    {
        return $this->multimediaRecordHandler;
    }
}