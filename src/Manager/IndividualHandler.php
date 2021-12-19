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
use Doctrine\ORM\EntityManagerInterface;

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
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * IndividualHandler constructor.
     * @param EventHandler $eventHandler
     * @param AttributeHandler $attributeHandler
     * @param IndividualNameHandler $individualNameHandler
     * @param SourceDataHandler $sourceDataHandler
     * @param MultimediaRecordHandler $multimediaRecordHandler
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventHandler $eventHandler, AttributeHandler $attributeHandler,
                                IndividualNameHandler $individualNameHandler, SourceDataHandler $sourceDataHandler,
                                MultimediaRecordHandler $multimediaRecordHandler, EntityManagerInterface $entityManager)
    {
        $this->eventHandler = $eventHandler;
        $this->attributeHandler = $attributeHandler;
        $this->individualNameHandler = $individualNameHandler;
        $this->sourceDataHandler = $sourceDataHandler;
        $this->multimediaRecordHandler = $multimediaRecordHandler;
        $this->entityManager = $entityManager;
    }


    /**
     * @param ArrayCollection $details
     * @return Individual
     */
    public function parse(ArrayCollection $details): Individual
    {
        $line = LineManager::getLineDetails($details->get(0));
        extract($line);
        if (!FileNameDiscriminator::getMerge()) {
            $identifier = trim($tag, '@');
            $individual = GedFileHandler::getIndividual($identifier);
        } else {
            $identifier = trim($tag, '@');
            If (mb_substr($identifier,0,1) === 'P') $identifier = 'I' . mb_substr($identifier, 1);
            $individual = GedFileHandler::getIndividual($identifier);
            dd($identifier, $individual,$details);
        }

        $q = 1;
        while ($details->containsKey($q)) {
            extract(LineManager::getLineDetails($details->get($q)));
            switch ($tag) {
                case 'NAME':
                    $name = ItemHandler::getSubItem($q, $details);
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
                    $event = ItemHandler::getSubItem($q, $details);
                    $q += $event->count() - 1;
                    $event = $this->getEventHandler()->parse($event, 'Individual');
                    $individual->addEvent($event);
                    break;
                case 'RESI':
                    $attribute = ItemHandler::getSubItem($q, $details);
                    $attribute = $this->getAttributeHandler()->parse($attribute, 'Individual');
                    $q += $attribute->getOffset() - 1;
                    break;
                case 'FAMS':
                    $identifier = trim($content, '@');
                    $family = GedFileHandler::getFamily($identifier);
                    GedFileHandler::addIndividualFamily($individual, $family, 'Spouse');
                    break;
                case 'FAMC':
                    $identifier = trim($content, '@');
                    $family = GedFileHandler::getFamily($identifier);
                    GedFileHandler::addIndividualFamily($individual, $family, 'Child');
                    break;
                case 'SOUR':
                    $sourceData = new SourceData();
                    $individual->addSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $details);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                case 'RIN':
                    $individual->setRecordKey($content);
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
                    $object = ItemHandler::getSubItem($q, $details);
                    $q += $object->count() - 1;
                    $record = $this->getMultimediaRecordHandler()->parse($object);
                    $individual->addMultimediaRecord($record);
                    break;
                case 'DSCR':
                    $individual->setDescription(explode(',',$content));
                    break;
                default:
                    dump(sprintf('I don\'t know how to handle a "%s" in "%s"', $tag, __CLASS__));
                    dd($details, $individual);

            }
            $q++;
        }
        $this->getEntityManager()->persist($individual);
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

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}