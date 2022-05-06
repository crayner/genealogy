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
 * Time: 09:30
 */

namespace App\Exception;

use Throwable;

/**
 * Class ParseException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 28/03/2021 09:30
 */
class ParseException extends \RuntimeException
{
    /**
     * ParseException constructor.
     * @param string $method
     * @param string $class
     */
    public function __construct(string $method = "", string $class = "") {
        $class = $class === "" ? $this->getFile() : $class;
        $method = $method === "" ? $this->getLine() : $method;
        $message = sprintf('A parse error occurred in class "%s" in method "%s".', $class, $method);
        parent::__construct($message, 0);
    }
}
