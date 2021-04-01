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
     * @param int $q
     * @param ArrayCollection $person
     * @param Individual $individual
     * @return IndividualName
     */
    public function parse(int $q, ArrayCollection $person, Individual $individual): IndividualName
    {
        $name = ItemHandler::getSubItem($q, $person);
        $individualName = new IndividualName();
        foreach ($name as $item) {
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
                    $individualName->setSource($content);
                    break;
                case '_MARNM':  //  My Heritage non standard.
                    $x = new IndividualName();
                    $individual->addName($x);
                    $x->setName($content)->setNameType('married');
                    break;
                default:
                    dump($this, $person);
                    dd(sprintf('The Individual Name part of "%s" can not be handled.', $tag));
            }
        }
        $individualName->setOffset($q + $name->count() - 1);
        return $individualName;
    }
}