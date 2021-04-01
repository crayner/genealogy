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
use App\Exception\EventException;
use Doctrine\Common\Collections\ArrayCollection;

class EventHandler
{
    /**
     * @var PlaceHandler
     */
    private PlaceHandler $placeHandler;

    /**
     * EventHandler constructor.
     * @param PlaceHandler $placeHandler
     */
    public function __construct(PlaceHandler $placeHandler)
    {
        $this->placeHandler = $placeHandler;
    }

    /**
     * @param ArrayCollection $eventDetails
     * @param string $source
     * @return Event
     */
    public function parse(ArrayCollection $eventDetails, string $source): Event
    {
        $event = new Event($source);
        $event->setOffset($eventDetails->count());
        $q = 0;
        while ($q < $eventDetails->count()) {
            $line = $eventDetails->get($q);
            extract(LineManager::getLineDetails($line));
            switch($tag) {
                case 'BIRT':
                    $event->setType('Birth');
                    break;
                case 'DEAT':
                    $event->setType('Death');
                    break;
                case 'DATE':
                    try {
                        $event->setDate(new \DateTimeImmutable($content));
                    } catch (\Exception $e) {
                        throw new EventException($event, sprintf('The event date (%s) is not valid.',$content));
                    }
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
                default:
                    dump(sprintf('Event handles the %s how?', $tag));
                    dd($eventDetails,$event,$source);
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
}
