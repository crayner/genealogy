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
 * Time: 10:43
 */

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class GedFileHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 10:45
 */
class GedFileHandler
{
    /**
     * @var string
     */
    private string $fileName;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $content;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $section;

    /**
     * @var ItemHandler
     */
    private ItemHandler $itemHandler;

    /**
     * GedFileHandler constructor.
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function parse()
    {
        $content = file($this->fileName);

        foreach ($content as $line) {
            $this->parseLine($line);
        }

        foreach ($this->getContent()->toArray() as $item) {
            $this->getItemHandler()->parse($item);
        }
    }

    private function parseLine(string $line)
    {
        $line = trim($line);
        $firstChar = substr($line, 0, 1);
        switch ($firstChar) {
            case '0':
                $this->createNewSection($line);
                break;
            default:
                return $this->addLine($line);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getContent(): ArrayCollection
    {
        return $this->content = isset($this->content) ? $this->content : new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    private function getSection(): ArrayCollection
    {
        return $this->section;
    }

    /**
     * @param string $line
     * @return GedFileHandler
     */
    public function createNewSection(string $line): GedFileHandler
    {
        if (isset($this->section)) {
            $this->getContent()->add($this->section);
        }
        $this->section = new ArrayCollection();

        $this->addLine($line);

        return $this;
    }

    /**
     * @param string $line
     * @return $this
     */
    private function addLine(string $line): GedFileHandler
    {
        $this->getSection()->add($line);
        return $this;
    }

    /**
     * @return ItemHandler
     */
    private function getItemHandler(): ItemHandler
    {
        return $this->itemHandler = isset($this->itemHandler) ? $this->itemHandler : new ItemHandler();
    }
}
