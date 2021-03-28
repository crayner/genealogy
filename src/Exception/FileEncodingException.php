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
 * Date: 28/03/2021
 * Time: 11:01
 */

namespace App\Exception;

/**
 * Class FileEncodingException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 28/03/2021 11:02
 */
class FileEncodingException extends \RuntimeException
{
    /**
     * FileEncodingException constructor.
     * @param string $encoding
     * @param string $defined
     */
    public function __construct(string $encoding, string $defined) {
        $message = sprintf('The file is not valid.  The encoding "%s" does not match the defined header encoding of %s', $encoding, $defined);
        parent::__construct($message, 0);
    }
}
