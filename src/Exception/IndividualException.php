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
 * Date: 30/03/2021
 * Time: 09:06
 */

namespace App\Exception;

use App\Entity\Individual;

/**
 * Class IndividualException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 09:06
 */
class IndividualException extends \RuntimeException
{
    /**
     * @var Individual
     */
    private Individual $individual;

    /**
     * IndividualException constructor.
     * @param Individual $individual
     * @param string|null $message
     */
    public function __construct(Individual $individual, string $message = null)
    {
        $this->individual = $individual;
        $message = $message ?: 'The Individual is not valid';
        parent::__construct($message);
    }
}
