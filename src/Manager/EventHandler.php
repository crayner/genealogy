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
 * Date: 31/03/2021
 * Time: 11:00
 */

namespace App\Manager;


use App\Entity\Event;
use App\Entity\Place;
use App\Entity\SourceData;
use App\Exception\EventException;
use Doctrine\Common\Collections\ArrayCollection;

class EventHandler
{
    /**
     * @var PlaceHandler
     */
    private PlaceHandler $placeHandler;

    /**
     * @var SourceDataHandler
     */
    private SourceDataHandler $sourceDataHandler;

    /**
     * EventHandler constructor.
     * @param PlaceHandler $placeHandler
     * @param SourceDataHandler $sourceDataHandler
     */
    public function __construct(PlaceHandler $placeHandler, SourceDataHandler $sourceDataHandler)
    {
        $this->placeHandler = $placeHandler;
        $this->sourceDataHandler = $sourceDataHandler;
    }

    /**
     * @param ArrayCollection $eventDetails
     * @return Event
     */
    public function parse(ArrayCollection $eventDetails): Event
    {
        $event = new Event();
        $event->setOffset($eventDetails->count());
        $q = 0;
        while ($q < $eventDetails->count()) {
            $line = $eventDetails->get($q);
            extract(LineManager::getLineDetails($line));
            switch($tag) {
                case 'BIRT':
                    $event->setType('Birth');
                    break;
                case 'MARR':
                    $event->setType('Marriage');
                    break;
                case 'DEAT':
                    $event->setType('Death');
                    break;
                case 'BURI':
                    $event->setType('Buried');
                    break;
                case 'BAPM':
                    $event->setType('Baptism');
                    break;
                case 'DIV':
                    $event->setType('Divorce');
                    break;
                case 'CHR':
                    $event->setType('Christening');
                    break;
                case 'ENGA':
                    $event->setType('Engagement');
                    break;
                case 'IMMI':
                    $event->setType('Immigration');
                    break;
                case 'OCCU':
                    $event->setType('Occupation');
                    $event->setName($content);
                    break;
                case 'EDUC':
                    $event->setType('Education');
                    $event->setName($content);
                    break;
                case 'DATE':
                    try {
                        $event->setDate(new \DateTimeImmutable($content));
                    } catch (\Exception $e) {
                        throw new EventException($event, sprintf('The event date (%s) is not valid.',$content));
                    }
                    break;
                case 'EVEN':
                    $event->setName($content);
                    break;
                case 'PLAC':
                    $place = ItemHandler::getSubItem($q, $eventDetails);
                    $place = $this->getPlaceHandler()->parse($place, 'Event');
                    $q += $place->getOffset() - 1;
                    $event->setPlace($place);
                    break;
                case 'AGE':
                    $event->setAge($content);
                    break;
                case 'CAUS':
                    $event->setCause($content);
                    break;
                case 'TYPE':
                    $event->setType($content);
                    break;
                case 'NOTE':
                    $event->setNote($content);
                    break;
                case 'ROLE':
                    $event->setRole($content);
                    break;
                case 'SOUR':
                    $identifier = trim($content, '@');
                    $source = GedFileHandler::getSource($identifier);
                    $sourceData = new SourceData($source);
                    $event->setSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $eventDetails);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                default:
                    dump(sprintf('Handling a (%s) is beyond the %s!', $tag, __CLASS__));
                    dd($eventDetails,$event);
            }
            $q++;
        }

        return $event;
    }

    /**
     * @return PlaceHandler
     */
    public function getPlaceHandler(): PlaceHandler
    {
        return $this->placeHandler;
    }

    /**
     * @return SourceDataHandler
     */
    public function getSourceDataHandler(): SourceDataHandler
    {
        return $this->sourceDataHandler;
    }
}
