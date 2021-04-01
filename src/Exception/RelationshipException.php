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
 * Time: 11:08
 */

namespace App\Exception;

use App\Entity\IndividualFamily;

/**
 * Class RelationshipException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 11:09
 */
class RelationshipException extends \RuntimeException
{
    /**
     * @var IndividualFamily
     */
    private IndividualFamily $individualFamily;

    /**
     * IndividualFamilyException constructor.
     * @param IndividualFamily $individualFamily
     * @param string|null $message
     */
    public function __construct(IndividualFamily $individualFamily, string $message = null)
    {
        $this->individualFamily = $individualFamily;
        $message = $message ?: 'The Individual Family Relationship is not valid';
        parent::__construct($message);
    }
}