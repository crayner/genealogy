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
 * Date: 27/03/2021
 * Time: 09:36
 */

namespace App\Doctrine\Types;

use Doctrine\DBAL\Types\StringType;

/**
 * Class EnumType
 * @package App\Doctrine\Types
 * @author  Craig Rayner <craig@craigrayner.com>
 * 27/03/2021 09:36
 */
class EnumType extends StringType
{
    public const ENUM = 'enum';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::ENUM;
    }
}
