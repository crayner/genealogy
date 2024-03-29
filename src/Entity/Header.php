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
 * Time: 15:15
 */

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;

/**
 * Class Header
 * @selectPure App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 24/03/2021 11:55
 * @ORM\Entity(repositoryClass="App\Repository\HeaderRepository")
 * @ORM\Table(name="header",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="gedcom",columns={"gedcom"})}
 * )
 * @UniqueEntity("gedcom")
 */
class Header
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)    
     */
    private string $id;

    /**
     * @var Gedcom
     * @ORM\OneToOne(targetEntity="App\Entity\Gedcom")
     * @ORM\JoinColumn(name="gedcom",referencedColumnName="id")
     */
    private Gedcom $gedcom;

    /**
     * @var string
     * @ORM\Column(length=32)
     */
    private string $char;

    /**
     * @var string
     * @ORM\Column(length=32)
     */
    private string $lang;

    /**
     * @var string
     * @ORM\Column(length=191)
     */
    private string $source;

    /**
     * @var string
     * @ORM\Column(length=191)
     */
    private string $destination;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="date_immutable")
     */
    private DateTimeImmutable $date;


    /**
     * @var string
     * @ORM\Column(length=191)
     */
    private string $file;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Gedcom
     */
    public function getGedcom(): Gedcom
    {
        return $this->gedcom = isset($this->gedcom) ? $this->gedcom : new Gedcom();
    }

    /**
     * @param Gedcom $gedcom
     */
    public function setGedcom(Gedcom $gedcom): void
    {
        $this->gedcom = $gedcom;
    }

    /**
     * @return string
     */
    public function getChar(): string
    {
        return $this->char;
    }

    /**
     * @param string $char
     * @return Header
     */
    public function setChar(string $char): Header
    {
        $this->char = $char;
        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     * @return Header
     */
    public function setLang(string $lang): Header
    {
        if (!in_array($lang, Languages::getNames())) throw new MissingResourceException(sprintf('The language "%s" is not valid.', $lang));
        $code = array_search($lang, Languages::getNames());
        $lang = Languages::getAlpha3Code($code);
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Header
     */
    public function setSource(string $source): Header
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     * @return Header
     */
    public function setDestination(string $destination): Header
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable $date
     * @return Header
     */
    public function setDate(DateTimeImmutable $date): Header
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return Header
     */
    public function setFile(string $file): Header
    {
        $this->file = $file;
        return $this;
    }
}
