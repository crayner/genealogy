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
 * Date: 25/03/2021
 * Time: 14:08
 */

namespace App\Entity;

use App\Exception\ParseException;
use App\Repository\GedcomRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class Gedcom
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 26/03/2021 12:56
 * @ORM\Entity(repositoryClass=GedcomRepository::class)
 * @ORM\Table(name="gedcom")
 */
class Gedcom
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
     * @var string
     * @ORM\Column(type="enum", length=32)
     */
    private string $version;

    /**
     * @var array|string[]
     */
    private static array $versionList = [
        '5.5',
        '5.5.1',
        '5.5.5'
    ];

    /**
     * @var string
     * @ORM\Column(type="enum", length=32)
     */
    private string $form;

    /**
     * @var array|string[]
     */
    private static array $formList = [
        'LINEAGE-LINKED'
    ];

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array|string[]
     */
    public static function getVersionList(): array
    {
        return self::$versionList;
    }

    /**
     * @param string $version
     * @return Gedcom
     */
    public function setVersion(string $version): self
    {
        if (in_array($version, self::getVersionList())) {
            $this->version = $version;
        } else {
            throw new ParseException(__METHOD__);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @return array|string[]
     */
    public static function getFormList(): array
    {
        return self::$formList;
    }

    /**
     * @param string $form
     * @return Gedcom
     */
    public function setForm(string $form): self
    {
        if (in_array($form, self::getFormList()))
            $this->form = $form;
        else
            throw new ParseException(__METHOD__);
        return $this;
    }

}
