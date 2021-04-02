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
 * Date: 2/04/2021
 * Time: 15:10
 */

namespace App\Manager;


use App\Entity\MultimediaRecord;
use Doctrine\Common\Collections\ArrayCollection;

class MultimediaRecordHandler
{
    public function parse(ArrayCollection $details): MultimediaRecord
    {
        $record = new MultimediaRecord();
        $q = 0;
        while ($details->containsKey($q)) {
            $line = $details->get($q);
            extract(LineManager::getLineDetails($line));
            switch ($tag) {
                case 'OBJE':
                    if (!is_null($content)) $record->setLink($content);
                    break;
                case 'FILE':
                    $record->createFileReference()->setReference($content);
                    break;
                case 'FORM':
                    $record->createFileReference()->setFormat($content);
                    break;
                case 'TITL':
                    $record->createFileReference()->setTitle($content);
                    break;
                case '_FILESIZE':
                case '_PHOTO_RIN':
                    $record->addExtra($tag,$content);
                    break;
                default:
                    dump(sprintf('Handling a %s is beyond the %s!',$tag, __CLASS__));
                    dd($tag,$content,$record,$details);
            }
            $q++;
        }

        return $record;
    }
}