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
 * Time: 10:31
 */

namespace App\Exception;

use App\Entity\Family;

/**
 * Class FamilyException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 10:32
 */
class FamilyException extends \RuntimeException
{
    /**
     * @var Family
     */
    private Family $family;

    /**
     * FamilyException constructor.
     * @param Family $family
     * @param string|null $message
     */
    public function __construct(Family $family, string $message = null)
    {
        $this->family = $family;
        $message = $message ?: 'The Family is not valid';
        parent::__construct($message);
    }
}
