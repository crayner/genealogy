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
 * Time: 15:00
 */

namespace App\Manager;

use App\Entity\Gedcom;
use App\Entity\Header;
use App\Exception\FileEncodingException;
use App\Exception\HeaderInvalidException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class HeadHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 15:00
 */
class HeadHandler
{
    /**
     * @var Gedcom
     */
    private Gedcom $gedcom;

    /**
     * @var Header
     */
    private Header $header;

    /**
     * @var string
     */
    private string $encoding;

    /**
     * HeadHandler constructor.
     * @param string $encoding
     */
    public function __construct(string $encoding)
    {
        $this->encoding = $encoding;
        $this->getHeader();
    }

    /**
     * @param ArrayCollection $item
     */
    public function parse(ArrayCollection $item)
    {
        $q = 0;
        while ($q<count($item)) {
            $line = $item[$q];
            extract(LineManager::getLineDetails($line));
            switch ($tag) {
                case 'HEAD':
                    break;
                case 'GEDC':
                    $q = $this->setGedcom($q, $item);
                    break;
                case 'CHAR':
                    if ($this->encoding === substr($line, 7))
                        $this->getHeader()->setChar(substr($line, 7));
                    else
                        throw new FileEncodingException($this->encoding, substr($line, 7));
                    break;
                case 'LANG':
                    $lang = substr($line, 7);
                    $this->getHeader()->setLang($lang);
                    break;
                case 'SOUR':
                    $q = $this->setSource($q, $item, substr($line, 7));
                    break;
                case 'DEST':
                    $this->getHeader()->setDestination(substr($line, 7));
                    break;
                case 'DATE':
                    try {
                        $date = new DateTimeImmutable(substr($line, 7));
                    } catch (\Exception $e) {
                        throw $e;
                    }
                    $this->getHeader()->setDate($date);
                    break;
                case 'FILE':
                    $this->getHeader()->setFile(substr($line, 7));
                    break;
                default:
                    if (strpos($tag, '_') !== 0)
                        throw new HeaderInvalidException(sprintf('The header was not able to handle a tag of "%s".', $tag));
            }
            $q++;
        }
    }

    /**
     * @return Gedcom
     */
    public function getGedcom(): Gedcom
    {
        return $this->gedcom;
    }

    /**
     * @param int $q
     * @param ArrayCollection $item
     * @return int
     */
    public function setGedcom(int $q, ArrayCollection $item): int
    {
        $this->gedcom = new Gedcom();
        $line = '';
        do {
            $q++;
            $line = $item->get($q);
            $tag = substr($line, 2, 4);
            switch ($tag) {
                case 'VERS':
                    $this->getGedcom()->setVersion(substr($line, 7));
                    break;
                case 'FORM':
                    $this->getGedcom()->setForm(substr($line, 7));
                    break;
            }
        } while (intval(substr($line, 0,1)) > 1);

        $this->getHeader()->setgedcom($this->getGedcom());
        return $q - 1;
    }

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header = isset($this->header) ? $this->header : new Header();
    }

    /**
     * @param int $q
     * @param ArrayCollection $item
     * @param string $source
     * @return int
     */
    public function setSource(int $q, ArrayCollection $item, string $source): int
    {
        $line = '';
        do {
            $q++;
            $line = $item->get($q);
        } while (intval(substr($line, 0,1)) > 1);

        $this->getHeader()->setSource($source);
        return $q - 1;
    }
}