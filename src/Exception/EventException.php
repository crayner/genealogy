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
 * Date: 31/03/2021
 * Time: 11:38
 */

namespace App\Exception;

use App\Entity\Event;

/**
 * Class EventException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 31/03/2021 11:38
 */
class EventException extends \RuntimeException
{
    /**
     * @var Event
     */
    private Event $event;

    /**
     * EventException constructor.
     * @param Event $event
     * @param string|null $message
     */
    public function __construct(Event $event, string $message = null)
    {
        $this->event = $event;
        $message = $message ?: 'The Event is not valid';
        parent::__construct($message);
    }
}
