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
 * Date: 3/04/2021
 * Time: 11:38
 */

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class SourceHandler
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 11:43
 */
class SourceHandler
{
    /**
     * @param ArrayCollection $details
     * @param bool $key
     */
    public function parse(ArrayCollection $details)
    {
        $line = LineManager::getLineDetails($details->get(0));
        extract($line);
        $identifier = trim($tag, '@');
        $source = GedFileHandler::getSource($identifier);

        $q = 1;
        while ($details->containsKey($q)) {
            extract(LineManager::getLineDetails($details->get($q)));
            switch ($tag) {
                case 'AUTH':
                    $source->setAuthority($content);
                    break;
                case 'TITL':
                    $source->setTitle($content);
                    break;
                case 'TEXT':
                    $source->setSourceText($content);
                    break;
                case 'RIN':
                    $source->setRecordKey($content);
                    break;
                case 'NOTE':
                    $source->setNote($content);
                    break;
                case 'PUBL':
                    $source->setPublish($content);
                    break;
                case '_TYPE':
                case '_MEDI':
                    $source->addExtra($tag,$content);
                    break;
                default:
                    dump(sprintf('Handling a (%s) is beyond %s?', $tag, __CLASS__));
                    dd($details, $source);

            }
            $q++;
        }

        return $source;
    }

}