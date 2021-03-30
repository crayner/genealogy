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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class IndividualNameHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 15:23
 */
class IndividualNameHandler
{
    /**
     * @var IndividualName
     */
    private IndividualName $individualName;

    /**
     * @param int $q
     * @param ArrayCollection $person
     * @return IndividualName
     */
    public function parse(int $q, ArrayCollection $person): IndividualName
    {
        $name = ItemHandler::getSubItem($q, $person);
        foreach ($name as $item) {
            extract(LineManager::getLineDetails($item));
            switch ($tag) {
                case 'NAME':
                    $this->newIndividualName();
                    $this->getIndividualName()->setName($content);
                    break;
                case 'TYPE':
                    $this->getIndividualName()->setNameType($content);
                    break;
                case 'GIVN':
                    $this->getIndividualName()->setGivenName($content);
                    break;
                case 'SURN':
                    $this->getIndividualName()->setSurname($content);
                    break;
                case 'NICK':
                    $this->getIndividualName()->setNickName($content);
                    break;
                case 'NPFX':
                    $this->getIndividualName()->setNamePrefix($content);
                    break;
                case 'SPFX':
                    $this->getIndividualName()->setSurnamePrefix($content);
                    break;
                case 'NOTE':
                    $this->getIndividualName()->setNote($content);
                    break;
                case 'SOUR':
                    $this->getIndividualName()->setSource($content);
                    break;
                default:
                    dump($this);
                    dd(sprintf('The Individual Name part of "%s" can not be handled.', $tag));
            }
        }
        $this->getIndividualName()->setOffset($q + $name->count() - 1);
        return $this->getIndividualName();
    }

    /**
     * @return IndividualName
     */
    public function getIndividualName(): IndividualName
    {
        return $this->individualName = isset($this->individualName) ? $this->individualName : new IndividualName();
    }

    /**
     * @param IndividualName $individualName
     * @return IndividualNameHandler
     */
    public function setIndividualName(IndividualName $individualName): IndividualNameHandler
    {
        $this->individualName = $individualName;
        return $this;
    }

    /**
     * @return IndividualName
     */
    public function newIndividualName(): IndividualName
    {
        $this->setIndividualName(new IndividualName());

        return $this->individualName;
    }
}