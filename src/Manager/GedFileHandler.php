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
use App\Entity\RepositoryRecord;
use App\Entity\Source;
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
     * @var DataManager
     */
    private static DataManager $dataManager;

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
     * @var string
     */
    private string $encoding;

    /**
     * GedFileHandler constructor.
     * @param ItemHandler $itemHandler
     * @param DataManager $dataManager
     */
    public function __construct(ItemHandler $itemHandler, DataManager $dataManager)
    {
        $this->itemHandler = $itemHandler;
        self::$dataManager = $dataManager;
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
        return $this->section = isset($this->section) ? $this->section : new ArrayCollection();
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
        return self::getDataManager()->getIndividuals();
    }

    /**
     * @param Individual $individual
     */
    public static function addIndividual(Individual $individual)
    {
        self::getDataManager()->addIndividual($individual);
    }

    /**
     * @param string $identifier
     * @return Individual
     */
    public static function getIndividual(string $identifier): Individual
    {
        return self::getDataManager()->getIndividual($identifier);
    }

    /**
     * @return ArrayCollection
     */
    public static function getFamilies(): ArrayCollection
    {
        return self::getDataManager()->getFamilies();
    }

    /**
     * @param Family $family
     */
    public static function addFamily(Family $family)
    {
        self::getDataManager()->addFamily($family);
    }

    /**
     * @param string $identifier
     * @return Family
     */
    public static function getFamily(string $identifier): Family
    {
        return self::getDataManager()->getFamily($identifier);    }

    /**
     * @return ArrayCollection
     */
    public static function getIndividualsFamilies(): ArrayCollection
    {
        return self::getDataManager()->getIndividualsFamilies();
    }

    /**
     * @param Individual $individual
     * @param Family $family
     * @param string $relationship
     * @return IndividualFamily
     */
    public static function addIndividualFamily(Individual $individual, Family $family, string $relationship): IndividualFamily
    {
        return self::getDataManager()->addIndividualFamily($individual, $family, $relationship);
    }

    /**
     * @param Individual $individual
     * @param Family $family
     * @param string $relationship
     * @return IndividualFamily
     */
    public static function getIndividualFamily(Individual $individual, Family $family, string $relationship): IndividualFamily
    {
        return self::getDataManager()->getIndividualFamily($individual,$family,$relationship);
    }

    /**
     * @return ArrayCollection
     */
    public static function getSources(): ArrayCollection
    {
        return self::getDataManager()->getSources();
    }

    /**
     * @param string $identifier
     * @return Source
     */
    public static function getSource(string $identifier): Source
    {
        return self::getDataManager()->getSource($identifier);
    }

    /**
     * @return DataManager
     */
    public static function getDataManager(): DataManager
    {
        return self::$dataManager;
    }

    /**
     * @return ArrayCollection
     */
    public static function getRepositories(): ArrayCollection
    {
        return self::getDataManager()->getRepositories();
    }

    /**
     * @param string $identifier
     * @return RepositoryRecord
     */
    public static function getRepository(string $identifier): RepositoryRecord
    {
        return self::getDataManager()->getRepository($identifier);
    }
}
