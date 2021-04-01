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
use App\Exception\AttributeException;
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
     * @var EventHandler
     */
    private EventHandler $eventHandler;

    /**
     * @var AttributeHandler
     */
    private AttributeHandler $attributeHandler;

    /**
     * @var IndividualNameHandler
     */
    private IndividualNameHandler $individualNameHandler;

    /**
     * IndividualHandler constructor.
     * @param IndividualNameHandler $individualNameHandler
     * @param EventHandler $eventHandler
     * @param AttributeHandler $attributeHandler
     */
    public function __construct(IndividualNameHandler $individualNameHandler, EventHandler $eventHandler, AttributeHandler $attributeHandler)
    {
        $this->individualNameHandler = $individualNameHandler;
        $this->eventHandler = $eventHandler;
        $this->attributeHandler = $attributeHandler;
    }


    /**
     * @param ArrayCollection $individual
     */
    public function parse(ArrayCollection $individual): Individual
    {
        $line = LineManager::getLineDetails($individual[0]);
        extract($line);
        $identifier = intval(trim($tag, 'IP@'));
        $this->setIndividual(GedFileHandler::getIndividual($identifier));

        $q = 1;
        while ($q < count($individual)) {
            extract(LineManager::getLineDetails($individual->get($q)));
            switch ($tag) {
                case 'NAME':
                    $individualName = $this->getIndividualNameHandler()->parse($q, $individual);
                    $this->getIndividual()->setName($individualName);
                    $q = $individualName->getOffset();
                    break;
                case 'SEX':
                    $this->getIndividual()->setGender($content);
                    break;
                case 'BIRT':
                    $event = ItemHandler::getSubItem($q, $individual);
                    $event = $this->getEventHandler()->parse($event, 'Individual');
                    $q += $event->getOffset() - 1;
                    break;
                case 'RESI':
                    $attribute = ItemHandler::getSubItem($q, $individual);
                    $attribute = $this->getAttributeHandler()->parse($attribute, 'Individual');
                    $q += $attribute->getOffset() - 1;
                    break;
                case 'FAMS':
                        $identifier = intval(trim($content, 'F@'));
                        $family = GedFileHandler::getFamily($identifier);
                        GedFileHandler::addIndividualFamily($this->getIndividual(), $family, 'Spouse');

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

    /**
     * @return EventHandler
     */
    public function getEventHandler(): EventHandler
    {
        return $this->eventHandler;
    }

    /**
     * @return AttributeHandler
     */
    public function getAttributeHandler(): AttributeHandler
    {
        return $this->attributeHandler;
    }
}