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
     * @var IndividualHandler
     */
    private IndividualHandler $individualHandler;

    /**
     * @var string
     */
    private string $encoding;

    /**
     * ItemHandler constructor.
     * @param HeadHandler $headHandler
     * @param IndividualHandler $individualHandler
     */
    public function __construct(HeadHandler $headHandler, IndividualHandler $individualHandler)
    {
        $this->headHandler = $headHandler;
        $this->individualHandler = $individualHandler;
    }

    /**
     * @param ArrayCollection $item
     */
    public function parse(ArrayCollection $item)
    {
        // item Type
        $first = preg_match("/HEAD|INDI$/",$item->first(), $matches);
        switch(key_exists(0, $matches) ? $matches[0] : '') {
            case 'HEAD':
                $this->getHeadHandler()->parse($item);
                dump($this);
                break;
            case 'INDI':
                $this->getIndividualHandler()->parse($item);
                break;
            default:
                dd($item, GedFileHandler::getDataManager());
        }
    }

    /**
     * @return HeadHandler
     */
    public function getHeadHandler(): HeadHandler
    {
        $this->headHandler->setEncoding($this->getEncoding());
        return $this->headHandler;
    }

    /**
     * @return IndividualHandler
     */
    public function getIndividualHandler(): IndividualHandler
    {
        return $this->individualHandler;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return ItemHandler
     */
    public function setEncoding(string $encoding): ItemHandler
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @param int $q
     * @param ArrayCollection $items
     * @return ArrayCollection
     */
    public static function getSubItem(int $q, ArrayCollection $items): ArrayCollection
    {
        $subItem = new ArrayCollection();
        $item = $items->get($q);
        $subItem->add($item);
        extract(LineManager::getLineDetails($item));
        $index = $level;
        do {
            $q++;
            if ($items->containsKey($q)) {
                $item = $items->get($q);
                extract(LineManager::getLineDetails($item));
                if ($level > $index) $subItem->add($item);
            }
        } while ($index < $level && $items->containsKey($q));

        return $subItem;
    }
}
