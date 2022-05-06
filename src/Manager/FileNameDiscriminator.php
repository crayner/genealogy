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
 * Date: 23/03/2021
 * Time: 10:30
 */

namespace App\Manager;

/**
 * Class FileNameDiscriminator
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 10:30
 */
class FileNameDiscriminator
{
    private static bool $merge = false;
    /**
     * @return string
     */
    public function execute(): string
    {
        return 'F:\websites\crayner\genealogy\880m5j_8814100z7k3665f88w882c.ged';
    }

    /**
     * @return string
     */
    public function merge(): string
    {
        self::$merge = true;
        return 'F:\websites\crayner\genealogy\Rayner 5.5.ged';
    }

    /**
     * @return bool
     */
    public static function getMerge(): bool
    {
        return self::$merge;
    }
}