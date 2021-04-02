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
 * Time: 10:34
 */

namespace App\Entity;

use App\Exception\IndividualNameException;
use App\Manager\ParameterManager;
use App\Repository\IndividualNameRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IndividualName
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 10:34
 * @ORM\Entity(repositoryClass=IndividualNameRepository::class)
 * @ORM\Table(name="individual_name", indexes={@ORM\Index(name="individual",columns={"individual"})})
 */
class IndividualName
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var Individual
     * @ORM\ManyToOne(targetEntity="App\Entity\Individual",inversedBy="names")
     * @ORM\JoinColumn(name="individual")
     */
    private Individual $individual;

    /**
     * @var string
     * @ORM\Column(length=120)
     */
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(type="enum",nullable=true)
     */
    private ?string $nameType;

    /**
     * @var array|string[]
     */
    private static array $nameTypeList = [
        'aka',
        'birth',
        'immigrant',
        'maiden',
        'married',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=120,nullable=true)
     */
    private ?string $givenName;

    /**
     * @var string|null
     * @ORM\Column(length=120,nullable=true)
     */
    private ?string $surname;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private ?string $nickName;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private ?string $namePrefix;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private ?string $surnamePrefix;

    /**
     * @var string|null
     * @ORM\Column(length=30,nullable=true)
     */
    private ?string $nameSuffix;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $note;

    /**
     * @var SourceData|null
     * @ORM\OneToOne(targetEntity="App\Entity\SourceData")
     * @ORM\JoinColumn(nullable=true,name="source")
     */
    private ?SourceData $source;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
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
     * @param bool $mirror
     * @return IndividualName
     */
    public function setIndividual(Individual $individual, bool $mirror = true): IndividualName
    {
        $this->individual = $individual;
        if ($mirror) $individual->addName($this, false);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return IndividualName
     */
    public function setName(string $name): IndividualName
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameType(): ?string
    {
        return isset($this->nameType) ? $this->nameType : null;
    }

    /**
     * @param string $nameType
     * @return IndividualName
     */
    public function setNameType(string $nameType): IndividualName
    {
        if (!in_array($nameType, self::getNameTypeList())) throw new IndividualNameException($this,sprintf('The name type, "%s", of the individual is not valid.', $nameType));
        $this->nameType = $nameType;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getNameTypeList(): array
    {
        return array_merge(self::$nameTypeList, ParameterManager::getParameter('name_type_list', []));
    }

    /**
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return isset($this->givenName) ? $this->givenName : null;
    }

    /**
     * @param string $givenName
     * @return IndividualName
     */
    public function setGivenName(string $givenName): IndividualName
    {
        if (mb_strlen($givenName) > 120) throw new IndividualNameException($this, sprintf('The given name, "%s", of the individual exceeds 120 (%s) characters in length.', $givenName, mb_strlen($givenName)));
        $this->givenName = $givenName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return IndividualName
     */
    public function setSurname(string $surname): IndividualName
    {
        if (mb_strlen($surname) > 120) throw new IndividualNameException($this,sprintf('The surname, "%s", of the individual exceeds 120 (%s) characters in length.', $surname, mb_strlen($surname)));
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    /**
     * @param string $nickName
     * @return IndividualName
     */
    public function setNickName(string $nickName): IndividualName
    {
        if (mb_strlen($nickName) > 30) throw new IndividualNameException($this,sprintf('The nick name, "%s", of the individual exceeds 30 (%s) characters in length.', $nickName, mb_strlen($nickName)));
        $this->nickName = $nickName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     * @return IndividualName
     */
    public function setNamePrefix(string $namePrefix): IndividualName
    {
        if (mb_strlen($namePrefix) > 30) throw new IndividualNameException($this,sprintf('The name prefix, "%s", of the individual exceeds 30 (%s) characters in length.', $namePrefix, mb_strlen($namePrefix)));
        $this->namePrefix = $namePrefix;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurnamePrefix(): ?string
    {
        return $this->surnamePrefix;
    }

    /**
     * @param string $surnamePrefix
     * @return IndividualName
     */
    public function setSurnamePrefix(string $surnamePrefix): IndividualName
    {
        if (mb_strlen($surnamePrefix) > 30) throw new IndividualNameException($this,sprintf('The surname prefix, "%s", of the individual exceeds 30 (%s) characters in length.', $surnamePrefix, mb_strlen($surnamePrefix)));
        $this->surnamePrefix = $surnamePrefix;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameSuffix(): ?string
    {
        return $this->nameSuffix;
    }

    /**
     * @param string $nameSuffix
     * @return IndividualName
     */
    public function setNameSuffix(string $nameSuffix): IndividualName
    {
        if (mb_strlen($nameSuffix) > 30) throw new IndividualNameException($this,sprintf('The name suffix, "%s", of the individual exceeds 30 (%s) characters in length.', $nameSuffix, mb_strlen($nameSuffix)));
        $this->nameSuffix = $nameSuffix;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string $note
     * @return IndividualName
     */
    public function setNote(string $note): IndividualName
    {
        if (mb_strlen($note) > 32767) throw new IndividualNameException($this,sprintf('The note, "%s", of the individual exceeds 32767 (%s) characters in length.', $note, mb_strlen($note)));
        $this->note = $note;
        return $this;
    }

    /**
     * @return SourceData|null
     */
    public function getSource(): ?SourceData
    {
        return isset($this->source) ? $this->source : null;
    }

    /**
     * @param SourceData|null $source
     * @return IndividualName
     */
    public function setSource(?SourceData $source): IndividualName
    {
        $this->source = $source;
        return $this;
    }
}
