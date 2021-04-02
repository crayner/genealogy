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
 * Date: 1/04/2021
 * Time: 09:31
 */

namespace App\Manager;

use App\Entity\Attribute;
use App\Entity\SourceData;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AttributeHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 09:31
 */
class AttributeHandler
{
    /**
     * @var SourceDataHandler
     */
    private SourceDataHandler $sourceDataHandler;

    /**
     * AttributeHandler constructor.
     * @param SourceDataHandler $sourceDataHandler
     */
    public function __construct(SourceDataHandler $sourceDataHandler)
    {
        $this->sourceDataHandler = $sourceDataHandler;
    }

    /**
     * @param ArrayCollection $attributeDetails
     * @param string $source
     * @return Attribute
     */
    public function parse(ArrayCollection $attributeDetails, string $source): Attribute
    {
        $attribute = new Attribute($source);
        $attribute->setOffset($attributeDetails->count());
        $q = 0;
        while ($q < $attributeDetails->count()) {
            $line = $attributeDetails->get($q);
            extract(LineManager::getLineDetails($line));
            switch($tag) {
                case 'RESI':
                    $attribute->setType('Residence');
                    break;
                case 'EMAIL':
                case 'EMAI':
                    $attribute->setEmail($content);
                    break;
                case 'PLAC':
                    $attribute->setPlace($content);
                    break;
                case 'SOUR':
                    $identifier = intval(trim($content, 'S@'));
                    $source = GedFileHandler::getSource($identifier);
                    $sourceData = new SourceData($source);
                    $attribute->setSource($sourceData);
                    $source = ItemHandler::getSubItem($q, $attributeDetails);
                    $q += $source->count() - 1;
                    $this->getSourceDataHandler()->parse($source, $sourceData);
                    break;
                default:
                    dump(sprintf('Attribute handles the %s how?', $tag));
                    dd($attribute,$attributeDetails);
            }
            $q++;
        }

        return $attribute;
    }

    /**
     * @return SourceDataHandler
     */
    public function getSourceDataHandler(): SourceDataHandler
    {
        return $this->sourceDataHandler;
    }
}