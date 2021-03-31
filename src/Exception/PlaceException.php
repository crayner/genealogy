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
 * Time: 08:41
 */

namespace App\Exception;

use App\Entity\Place;

/**
 * Class PlaceException
 * @package App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 08:47
 */
class PlaceException extends \RuntimeException
{
    /**
     * @var Place
     */
    private Place $place;

    /**
     * PlaceException constructor.
     * @param Place $place
     * @param string|null $message
     */
    public function __construct(Place $place, string $message = null)
    {
        $this->place = $place;
        $message = $message ?: 'The place is not valid';
        parent::__construct($message);
    }
}