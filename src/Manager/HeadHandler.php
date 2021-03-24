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
 * Date: 23/03/2021
 * Time: 15:00
 */

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class HeadHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 15:00
 */
class HeadHandler
{
    /**
     * @param ArrayCollection $item
     */
    public function parse(ArrayCollection $item)
    {
        foreach ($item as $line) {
            $tag = substr($line, 2, 4);
            switch ($tag) {
                case 'HEAD':
                    break;
                default:
                    dd(__CLASS__ . ': How to handle a '. $tag, $item);
            }
        }
    }
}