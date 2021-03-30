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
 * Time: 09:09
 */

namespace App\Manager;

use App\Entity\Individual;
use App\Entity\IndividualName;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class IndividualHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 09:37
 */
class IndividualHandler
{
    /**
     * @var Individual
     */
    private Individual $individual;

    /**
     * @var IndividualNameHandler
     */
    private IndividualNameHandler $individualNameHandler;

    /**
     * IndividualHandler constructor.
     * @param IndividualNameHandler $individualNameHandler
     */
    public function __construct(IndividualNameHandler $individualNameHandler)
    {
        $this->individualNameHandler = $individualNameHandler;
    }


    /**
     * @param ArrayCollection $individual
     */
    public function parse(ArrayCollection $individual): Individual
    {
        $line = LineManager::getLineDetails($individual[0]);
        extract($line);
        $this->setIndividual(new Individual(intval(trim($tag, 'I@'))));

        $q = 1;
        while ($q < count($individual)) {
            extract(LineManager::getLineDetails($individual[$q]));
            switch ($tag) {
                case 'NAME':
                    $individualName = $this->getIndividualNameHandler()->parse($q, $individual);
                    $this->getIndividual()->setName($individualName);
                    $q = $individualName->getOffset();
                    break;
                default:
                    dump(sprintf('I don\'t know how to handle a "%s" in "%s"', $tag, __CLASS__));
                    dd($individual, $this->getIndividual());

            }
            $q++;
        }

        return $this->getIndividual();
    }

    /**
     * @return Individual
     */
    public function getIndividual(): Individual
    {
        return $this->individual;
    }

    /**
     * @param Individual $individual
     * @return IndividualHandler
     */
    public function setIndividual(Individual $individual): IndividualHandler
    {
        $this->individual = $individual;
        return $this;
    }

    /**
     * @return IndividualName
     */
    public function getIndividualName(): IndividualName
    {
        return $this->getIndividual()->getName();
    }

    /**
     * @return IndividualNameHandler
     */
    public function getIndividualNameHandler(): IndividualNameHandler
    {
        return $this->individualNameHandler;
    }

}