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
     * IndividualHandler constructor.
     * @param IndividualNameHandler $individualNameHandler
     * @param EventHandler $eventHandler
     * @param AttributeHandler $attributeHandler
     */
    public function __construct(IndividualNameHandler $individualNameHandler, EventHandler $eventHandler, AttributeHandler $attributeHandler, SourceDataHandler $sourceDataHandler)
    {
        $this->individualNameHandler = $individualNameHandler;
        $this->eventHandler = $eventHandler;
        $this->attributeHandler = $attributeHandler;
        $this->sourceDataHandler = $sourceDataHandler;
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
                    $individualName = $this->getIndividualNameHandler()->parse($q, $individualDetails, $individual);
                    $individual->addName($individualName);
                    $q = $individualName->getOffset();
                    break;
                case 'SEX':
                    $individual->setGender($content);
                    break;
                case 'DEAT':
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
                    $identifier = intval(trim($content, 'S@'));
                    $source = GedFileHandler::getSource($identifier);
                    $sourceData = new SourceData($source);
                    $individual->addSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $individualDetails);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                case 'RIN':
                    $individual->setRecordKey(intval($content));
                    break;
                case '_UID':  // My Heritage Site crap
                case '_UPD':  // My Heritage Site crap
                    // Ignore rubbish
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
     * @return IndividualName
     */
    public function getIndividualName(): IndividualName
    {
        return $individual->getName();
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
}