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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Header
 * @package App\Entity
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
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
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
        return $this->gedcom;
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
}
