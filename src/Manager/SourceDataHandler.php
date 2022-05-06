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
 * Time: 15:26
 */

namespace App\Manager;

use App\Entity\SourceData;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class SourceDataHandler
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 16:02
 */
class SourceDataHandler
{
    /**
     * @var DataHandler
     */
    private DataHandler $dataHandler;

    /**
     * SourceDataHandler constructor.
     * @param DataHandler $dataHandler
     */
    public function __construct(DataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param ArrayCollection $sourceDetails
     * @param SourceData $source
     * @return SourceData
     */
    public function parse(ArrayCollection $sourceDetails, SourceData $source): SourceData
    {
        $q = 0;
        while ($sourceDetails->containsKey($q)) {
            extract(LineManager::getLineDetails($sourceDetails->get($q)));
            switch ($tag) {
                case 'SOUR':
                    $identifier = trim($content, '@');
                    if (mb_strlen($identifier) > 0) {
                        $x = GedFileHandler::getSource($identifier);
                        $source->setSource($x);
                    }
                    break;
                case 'PAGE':
                    $source->setPage($content);
                    break;
                case 'QUAY':
                    $source->setQualityOfData($content);
                    break;
                case 'DATA':
                    $data = ItemHandler::getSubItem($q, $sourceDetails);
                    $q += $data->count() - 1;
                    $data = $this->getDataHandler()->parse($data, $source);
                    break;
                case 'NOTE':
                    $source->setNote($content);
                    break;
                case 'CONC':
                case 'CONT':
                    $source->concatNote($content);
                    break;
                case '_APID': // Ancestry Data
                    $source->concatNote(' '.$tag.' '.$content);
                    break;
                default:
                    dump(sprintf('Handling a %s is beyond the SourceDataHandler!',$tag));
                    dd($sourceDetails,$source);
            }
            $q++;
        }
        return $source;
    }

    /**
     * @return DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
}