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
 * Time: 15:38
 */

namespace App\Exception;

use App\Entity\SourceData;

/**
 * Class SourceDataException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 15:39
 */
class SourceDataException extends \RuntimeException
{
    /**
     * @var SourceData
     */
    private SourceData $sourceData;

    /**
     * SourceDataException constructor.
     * @param SourceData $sourceData
     * @param string|null $message
     */
    public function __construct(SourceData $sourceData, string $message = null)
    {
        $this->sourceData = $sourceData;
        $message = $message ?: 'The Source Data is not valid';
        parent::__construct($message);
    }
}
