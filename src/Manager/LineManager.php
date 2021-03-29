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
 * Date: 29/03/2021
 * Time: 16:44
 */

namespace App\Manager;


class LineManager
{
    /**
     * @var string
     */
    private static string $level;

    /**
     * @var string
     */
    private static string $tag;

    /**
     * @var string
     */
    private static string $content;

    /**
     * @param string $line
     * @return array
     */
    public static function getLineDetails(string $line): array
    {
        $items = explode(' ', $line);
        if (key_exists(0, $items)) {
            self::setLevel($items[0]);
            unset($items[0]);
        }

        if (key_exists(1, $items)) {
            self::setTag($items[1]);
            unset($items[1]);
        }

        if (key_exists(2, $items)) {
            self::setContent(implode(' ',$items));
        }

        return [
            'level' => self::getLevel(),
            'tag' => self::getTag(),
            'content' => self::getContent(),
        ];
    }

    /**
     * @return string|null
     */
    public static function getLevel(): ?string
    {
        return isset(self::$level) ? self::$level : null;
    }

    /**
     * @param string $level
     */
    public static function setLevel(string $level): void
    {
        self::$level = $level;
    }

    /**
     * @return string|null
     */
    public static function getTag(): ?string
    {
        return isset(self::$tag) && self::$tag !== '' ? self::$tag : null;
    }

    /**
     * @param string $tag
     */
    public static function setTag(string $tag): void
    {
        self::$tag = $tag;
    }

    /**
     * @return string|null
     */
    public static function getContent(): ?string
    {
        return isset(self::$content) && self::$content !== '' ? self::$content : null;
    }

    /**
     * @param string $content
     */
    public static function setContent(string $content): void
    {
        self::$content = str_replace(['@@'], ['@'], $content);
    }
}
