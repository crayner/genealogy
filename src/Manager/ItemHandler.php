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
 * Time: 14:33
 */

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ItemHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 14:45
 */
class ItemHandler
{
    /**
     * @var HeadHandler
     */
    private HeadHandler $headHandler;

    /**
     * @param ArrayCollection $item
     */
    public function parse(ArrayCollection $item)
    {
        // item Type
        $first = preg_match("/HEAD/",$item->first(), $matches);
        switch(key_exists(0, $matches) ? $matches[0] : '') {
            case 'HEAD':
                $this->getHeadHandler()->parse($item);
                break;
            default:
                dd($item);
        }
    }

    /**
     * @return HeadHandler
     */
    public function getHeadHandler(): HeadHandler
    {
        return $this->headHandler = isset($this->headHandler) ? $this->headHandler : new HeadHandler();
    }
}