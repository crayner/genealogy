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

use App\Entity\MultimediaFile;

/**
 * Class MultiMediaFileException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 15:31
 */
class MultiMediaFileException extends \RuntimeException
{
    /**
     * @var MultimediaFile
     */
    private MultimediaFile $multimediaFile;

    /**
     * MultimediaFileException constructor.
     * @param MultimediaFile $multimediaFile
     * @param string|null $message
     */
    public function __construct(MultimediaFile $multimediaFile, string $message = null)
    {
        $this->multimediaFile = $multimediaFile;
        $message = $message ?: 'The Multimedia File is not valid';
        parent::__construct($message);
    }
}
