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
 * Date: 2/04/2021
 * Time: 13:23
 */

namespace App\Manager;

use App\Entity\Family;
use App\Entity\Individual;
use App\Entity\IndividualFamily;
use App\Entity\Source;
use App\Exception\ParseException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class DataManager
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 13:23
 */
class DataManager
{
    /**
     * @var ArrayCollection
     */
    private ArrayCollection $individuals;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $families;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $individualsFamilies;

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $sources;

    /**
     * @return ArrayCollection
     */
    public function getIndividuals(): ArrayCollection
    {
        return $this->individuals = isset($this->individuals) ? $this->individuals : new ArrayCollection();
    }

    /**
     * @param Individual $individual
     */
    public function addIndividual(Individual $individual)
    {
        if ($this->getIndividuals()->containsKey($individual->getIdentifier())) return;

        $this->individuals->set($individual->getIdentifier(), $individual);
    }

    /**
     * @param int $identifier
     * @return Individual
     */
    public function getIndividual(int $identifier): Individual
    {
        if ($identifier < 1) throw new ParseException(__METHOD__, __CLASS__);
        if ($this->getIndividuals()->containsKey($identifier)) return $this->individuals->get($identifier);

        $individual = new Individual($identifier);

        $this->addIndividual($individual);

        return $individual;
    }

    /**
     * @return ArrayCollection
     */
    public function getFamilies(): ArrayCollection
    {
        return $this->families = isset($this->families) ? $this->families : new ArrayCollection();
    }

    /**
     * @param Family $family
     */
    public function addFamily(Family $family)
    {
        if ($this->getFamilies()->containsKey($family->getIdentifier())) return;

        $this->families->set($family->getIdentifier(), $family);
    }

    /**
     * @param int $identifier
     * @return Family
     */
    public function getFamily(int $identifier): Family
    {
        if ($identifier < 1) throw new ParseException(__METHOD__, __CLASS__);
        if ($this->getFamilies()->containsKey($identifier)) return $this->families->get($identifier);

        $family = new Family($identifier);

        $this->addFamily($family);

        return $family;
    }

    /**
     * @return ArrayCollection
     */
    public function getIndividualsFamilies(): ArrayCollection
    {
        return $this->individualsFamilies = isset($this->individualsFamilies) ? $this->individualsFamilies : new ArrayCollection();
    }

    /**
     * @param Individual $individual
     * @param Family $family
     * @param string $relationship
     * @return IndividualFamily
     */
    public function addIndividualFamily(Individual $individual, Family $family, string $relationship): IndividualFamily
    {
        $x = $this->getIndividualsFamilies()->filter(function (IndividualFamily $indfam) use ($individual) {
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
        $this->getIndividualsFamilies()->add($indfam);

        return $indfam;
    }

    /**
     * @return ArrayCollection
     */
    public function getSources(): ArrayCollection
    {
        return $this->sources = isset($this->sources) ? $this->sources : new ArrayCollection();
    }

    /**
     * @param int $identifier
     * @return Source
     */
    public function getSource(int $identifier): Source
    {
        if ($this->getSources()->containsKey($identifier)) return $this->sources->get($identifier);

        $source = new Source($identifier);

        $this->sources->set($identifier, $source);
        return $source;
    }
}
