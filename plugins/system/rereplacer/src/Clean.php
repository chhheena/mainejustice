<?php
/**
 * @package         ReReplacer
 * @version         12.4.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ReReplacer;

defined('_JEXEC') or die;

class Clean
{
    public static function cleanStringReplace(&$string, $is_regex = true)
    {
        if ( ! $is_regex)
        {
            $string = str_replace(['\\', '\\\\#', '$'], ['\\\\', '\\#', '\\$'], $string);
        }

        self::cleanString($string);
    }

    public static function cleanString(&$string)
    {
        $string = str_replace(['[:space:]', '\[\:space\:\]', '[[space]]', '\[\[space\]\]'], ' ', $string);
        $string = str_replace(['[:comma:]', '\[\:comma\:\]', '[[comma]]', '\[\[comma\]\]'], ',', $string);
        $string = str_replace(['[:newline:]', '\[\:newline\:\]', '[[newline]]', '\[\[newline\]\]'], "\n", $string);
        $string = str_replace('[:REGEX_ENTER:]', '\\n', $string);
    }
}
