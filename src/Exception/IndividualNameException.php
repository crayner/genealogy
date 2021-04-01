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
 * Time: 08:54
 */

namespace App\Exception;

use App\Entity\IndividualName;

/**
 * Class IndividualNameException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 08:55
 */
class IndividualNameException extends \RuntimeException
{
    /**
     * @var IndividualName
     */
    private IndividualName $individualName;

    /**
     * IndividualNameException constructor.
     * @param IndividualName $individualName
     * @param string|null $message
     */
    public function __construct(IndividualName $individualName, string $message = null)
    {
        $this->individualName = $individualName;
        $message = $message ?: 'The Individual Name is not valid';
        parent::__construct($message);
    }
}