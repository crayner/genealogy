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
use App\Exception\AttributeException;
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
                default:
                    dump(sprintf('Attribute handles the %s how?', $tag));
                    dd($attribute,$attributeDetails);
            }
            $q++;
        }

        return $attribute;
    }

}