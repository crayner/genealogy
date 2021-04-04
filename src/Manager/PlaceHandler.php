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
 * Time: 17:01
 */

namespace App\Manager;

use App\Entity\Place;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PlaceHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 31/03/2021 17:01
 */
class PlaceHandler
{
    /**
     * @param ArrayCollection $placeDetails
     * @return Place
     */
    public function parse(ArrayCollection $placeDetails): Place
    {
        $place = new Place();
        $place->setOffset($placeDetails->count());
        $q = 0;
        while ($q < $placeDetails->count() && $placeDetails->containsKey($q)) {
            extract(LineManager::getLineDetails($placeDetails->get($q)));
            switch ($tag) {
                case 'EVEN':
                case 'PLAC':
                    $place->setName($content);
                    break;
                default:
                    dump(sprintf('There\'s no place like %s', $tag));
                    dd($place, $placeDetails);
            }
            $q++;
        }

        return $place;
    }
}
