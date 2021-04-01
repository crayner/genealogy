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

use App\Entity\Family;
use App\Entity\Individual;
use App\Entity\IndividualFamily;
use App\Entity\Source;
use App\Exception\ParseException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;

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
     * @var ArrayCollection
     */
    private static ArrayCollection $individuals;

    /**
     * @var ArrayCollection
     */
    private static ArrayCollection $families;

    /**
     * @var ArrayCollection
     */
    private static ArrayCollection $individualsFamilies;

    /**
     * @var ArrayCollection
     */
    private static ArrayCollection $sources;

    /**
     * @var string
     */
    private string $encoding;

    /**
     * GedFileHandler constructor.
     * @param ItemHandler $itemHandler
     */
    public function __construct(ItemHandler $itemHandler)
    {
        $this->itemHandler = $itemHandler;
    }

    public function parse()
    {
        $file = new File($this->getFileName());

        $this->setEncoding(mb_detect_encoding($file->getContent()));

        $content = file($this->fileName);
        foreach ($content as $line) {
            $this->parseLine($line);
        }

        foreach ($this->getContent()->toArray() as $item) {
            $this->getItemHandler()->parse($item);
        }
        dd($this);
    }

    /**
     * @param string $line
     * @return $this|GedFileHandler
     */
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
        $this->itemHandler->setEncoding($this->getEncoding());
        return $this->itemHandler;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return GedFileHandler
     */
    public function setFileName(string $fileName): GedFileHandler
    {
        $this->fileName = $fileName;
        return $this;
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
     * @return GedFileHandler
     */
    public function setEncoding(string $encoding): GedFileHandler
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public static function getIndividuals(): ArrayCollection
    {
        return self::$individuals = isset(self::$individuals) ? self::$individuals : new ArrayCollection();
    }

    /**
     * @param Individual $individual
     */
    public static function addIndividual(Individual $individual)
    {
        if (self::getIndividuals()->containsKey($individual->getIdentifier())) return;

        self::$individuals->set($individual->getIdentifier(), $individual);
    }

    /**
     * @param int $identifier
     * @return Individual
     */
    public static function getIndividual(int $identifier): Individual
    {
        if ($identifier < 1) throw new ParseException(__METHOD__, __CLASS__);
        if (self::getIndividuals()->containsKey($identifier)) return self::$individuals->get($identifier);

        $individual = new Individual($identifier);

        self::addIndividual($individual);

        return $individual;
    }

    /**
     * @return ArrayCollection
     */
    public static function getFamilies(): ArrayCollection
    {
        return self::$families = isset(self::$families) ? self::$families : new ArrayCollection();
    }

    /**
     * @param Family $family
     */
    public static function addFamily(Family $family)
    {
        if (self::getFamilies()->containsKey($family->getIdentifier())) return;

        self::$families->set($family->getIdentifier(), $family);
    }

    /**
     * @param int $identifier
     * @return Family
     */
    public static function getFamily(int $identifier): Family
    {
        if ($identifier < 1) throw new ParseException(__METHOD__, __CLASS__);
        if (self::getFamilies()->containsKey($identifier)) return self::$families->get($identifier);

        $family = new Family($identifier);

        self::addFamily($family);

        return $family;
    }

    /**
     * @return ArrayCollection
     */
    public static function getIndividualsFamilies(): ArrayCollection
    {
        return self::$individualsFamilies = isset(self::$individualsFamilies) ? self::$individualsFamilies : new ArrayCollection();
    }

    /**
     * @param Individual $individual
     * @param Family $family
     * @param string $relationship
     * @return IndividualFamily
     */
    public static function addIndividualFamily(Individual $individual, Family $family, string $relationship): IndividualFamily
    {
        $x = self::getIndividualsFamilies()->filter(function (IndividualFamily $indfam) use ($individual) {
            if ($individual ===$indfam->getIndividual()) return $indfam;
        });

        $y = $x->filter(function(IndividualFamily $indfam) use ($family) {
            if ($family === $indfam->getFamily()) return $indfam;
        });

        if ($y->count() === 1) return $y->first();

        if ($y->count() > 1) throw new ParseException(__METHOD__,__CLASS__);

        $indfam = new IndividualFamily($individual, $family, $relationship);

        $family->addIndividual($indfam);
        $individual->addFamily($indfam);
        self::getIndividualsFamilies()->add($indfam);

        return $indfam;
    }

    /**
     * @return ArrayCollection
     */
    public static function getSources(): ArrayCollection
    {
        return self::$sources = isset(self::$sources) ? self::$sources : new ArrayCollection();
    }

    /**
     * @param int $identifier
     * @return Source
     */
    public static function getSource(int $identifier): Source
    {
        if (self::getSources()->containsKey($identifier)) return self::$sources->get($identifier);

        $source = new Source($identifier);

        self::$sources->set($identifier, $source);
        return $source;
    }
}
