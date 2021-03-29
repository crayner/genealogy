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
 * Time: 08:57
 */

namespace App\Entity;

use App\Exception\FileEncodingException;
use App\Exception\IndividualException;
use App\Repository\IndividualRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Individual
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 08:58
 * @ORM\Entity(repositoryClass=IndividualRepository::class)
 * @ORM\Table(name="individual")
 */
class Individual
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private int $identifier;

    /**
     * Individual constructor.
     * @param int $identifier
     */
    public function __construct(int $identifier = 0)
    {
        if ($identifier > 0) $this->identifier = $identifier;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return Individual
     */
    public function setId(?string $id): Individual
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        if (!isset($this->identifier)) throw new IndividualException('The individual does not have a valid identifier.');
        return $this->identifier;
    }

    /**
     * @param int $identifier
     * @return Individual
     */
    public function setIdentifier(int $identifier): Individual
    {
        $this->identifier = $identifier;
        return $this;
    }
}
