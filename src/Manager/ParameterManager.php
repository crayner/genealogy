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
 * Time: 14:11
 */

namespace App\Manager;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ParameterManager
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 30/03/2021 14:13
 */
class ParameterManager
{
    /**
     * @var ParameterBagInterface
     */
    private static ParameterBagInterface $bag;

    /**
     * ParameterManager constructor.
     * @param ParameterBagInterface $bag
     */
    public function __construct(ParameterBagInterface $bag)
    {
        self::$bag = $bag;
    }

    /**
     * @return ParameterBagInterface
     */
    protected static function getBag(): ParameterBagInterface
    {
        return self::$bag;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public static function getParameter(string $name, $default = null)
    {
        return self::getBag()->has($name) ? self::getBag()->get($name) : $default;
    }
}
