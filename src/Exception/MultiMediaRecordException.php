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
 * Time: 14:53
 */

namespace App\Exception;

use App\Entity\MultimediaRecord;

/**
 * Class MultiMediaRecordException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 14:54
 */
class MultiMediaRecordException extends \RuntimeException
{
    /**
     * @var MultimediaRecord
     */
    private MultimediaRecord $multimediaRecord;

    /**
     * MultimediaRecordException constructor.
     * @param MultimediaRecord $multimediaRecord
     * @param string|null $message
     */
    public function __construct(MultimediaRecord $multimediaRecord, string $message = null)
    {
        $this->multimediaRecord = $multimediaRecord;
        $message = $message ?: 'The Multimedia Record is not valid';
        parent::__construct($message);
    }
}
