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
 * Time: 15:52
 */

namespace App\Manager;

use App\Entity\Data;
use App\Entity\SourceData;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

/**
 * Class DataHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 16:06
 */
class DataHandler
{
    /**
     * @param ArrayCollection $dataDetails
     * @param SourceData $source
     * @return Data
     */
    public function parse(ArrayCollection $dataDetails, SourceData $source): Data
    {
        $q = 1;
        $data = new Data();
        while ($dataDetails->containsKey($q)) {
            extract(LineManager::getLineDetails($dataDetails->get($q)));
            switch ($tag) {
                case 'DATE':
                    try {
                        $data->setDate(new DateTimeImmutable($content));
                    } catch (Exception $e) {
                        throw $e;
                    }
                    break;
                case 'TEXT':
                    $data->setSourceData($source);
                    $data->setContent($content);
                    break;
                default:
                    dump(sprintf('(%s) is beyond the scope of %s', $tag, __CLASS__. '::' . __METHOD__));
                    dd($dataDetails,$data);
            }
            $q++;
        }
        return $data;
    }
}
