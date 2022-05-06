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

use App\Entity\Source;

/**
 * Class SourceException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 3/04/2021 11:58
 */
class SourceException extends \RuntimeException
{
    /**
     * @var Source
     */
    private Source $source;

    /**
     * SourceException constructor.
     * @param Source $source
     * @param string|null $message
     */
    public function __construct(Source $source, string $message = null)
    {
        $this->source = $source;
        $message = $message ?: 'The Source is not valid';
        parent::__construct($message);
    }
}
