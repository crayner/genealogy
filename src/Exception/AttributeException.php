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
 * Time: 09:24
 */

namespace App\Exception;

use App\Entity\Attribute;

/**
 * Class AttributeException
 * @selectPure App\Exception
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 09:25
 */
class AttributeException extends \RuntimeException
{
    /**
     * @var Attribute
     */
    private Attribute $attribute;

    /**
     * AttributeException constructor.
     * @param Attribute $attribute
     * @param string|null $message
     */
    public function __construct(Attribute $attribute, string $message = null)
    {
        $this->attribute = $attribute;
        $message = $message ?: 'The Attribute is not valid';
        parent::__construct($message);
    }
}
