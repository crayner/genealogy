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
 * Time: 16:32
 */

namespace App\Manager;
use App\Entity\Address;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AddressHandler
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 16:32
 */
class AddressHandler
{
    /**
     * @param ArrayCollection $details
     * @return Address
     */
    public function parse(ArrayCollection $details): Address
    {
        $address = new Address();
        if ($details->count() === 1) {
            extract(LineManager::getLineDetails($details->first()));
            $address->setLine1($content);
            return $address;
        }

        $q = 0;
        while ($details->containsKey($q)) {
            extract(LineManager::getLineDetails($details->get($q)));
            switch ($tag) {
                default:
                    dump(sprintf('Handling a (%s) is beyond %s!', $tag, __CLASS__));
                    dd($details, $address);

            }
            $q++;
        }

        return $address;
    }
}
