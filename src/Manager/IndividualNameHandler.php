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
 * Date: 30/03/2021
 * Time: 14:35
 */

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\IndividualName;
use App\Entity\SourceData;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class IndividualNameHandler
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 15:23
 */
class IndividualNameHandler
{
    /**
     * @var SourceDataHandler
     */
    private SourceDataHandler $sourceDataHandler;

    /**
     * IndividualNameHandler constructor.
     * @param SourceDataHandler $sourceDataHandler
     */
    public function __construct(SourceDataHandler $sourceDataHandler)
    {
        $this->sourceDataHandler = $sourceDataHandler;
    }

    /**
     * @param ArrayCollection $name
     * @param Individual $individual
     * @return IndividualName
     */
    public function parse(ArrayCollection $name, Individual $individual): IndividualName
    {
        $individualName = new IndividualName();
        $q = 0;
        while ($name->containsKey($q)) {
            $item = $name->get($q);
            extract(LineManager::getLineDetails($item));
            switch ($tag) {
                case 'NAME':
                    $individualName->setName($content);
                    break;
                case 'TYPE':
                    $individualName->setNameType($content);
                    break;
                case 'GIVN':
                    $individualName->setGivenName($content);
                    break;
                case 'SURN':
                    $individualName->setSurname($content);
                    break;
                case 'NICK':
                    $individualName->setNickName($content);
                    break;
                case 'NPFX':
                    $individualName->setNamePrefix($content);
                    break;
                case 'SPFX':
                    $individualName->setSurnamePrefix($content);
                    break;
                case 'NOTE':
                    $individualName->setNote($content);
                    break;
                case 'SOUR':
                    $identifier = trim($content, '@');
                    $source = GedFileHandler::getSource($identifier);
                    $sourceData = new SourceData($source);
                    $individualName->setSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $name);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                case '_MARNM':  //  My Heritage non standard.
                    $x = new IndividualName();
                    $individual->addName($x);
                    $x->setName($content)->setNameType('married');
                    break;
                default:
                    dump($this, $name);
                    dd(sprintf('The Individual Name part of "%s" can not be handled.', $tag));
            }
            $q++;
        }
        return $individualName;
    }

    /**
     * @return SourceDataHandler
     */
    public function getSourceDataHandler(): SourceDataHandler
    {
        return $this->sourceDataHandler;
    }
}