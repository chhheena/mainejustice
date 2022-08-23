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

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;

class Protect
{
    static $protect_end   = '<!-- END: RR_PROTECT -->';
    static $protect_start = '<!-- START: RR_PROTECT -->';

    public static function stringToProtectedArray($string, &$item, $onlyform = false)
    {
        $string_array = [$string];

        if ( ! $item->enable_in_edit_forms && RL_Document::isEditPage())
        {
            // Protect complete adminForm (to prevent ReReplacer messing stuff up when editing articles and such)
            $search = RL_Protect::getFormRegex();
            $search = '(' . $search . '.*?</form>)';
            self::protectArrayByRegex($string_array, $search, '', true);
        }

        if ($onlyform)
        {
            return $string_array;
        }


        // Protect everything between the {noreplace} tags
        $search = '(\{noreplace\}.*?\{/noreplace\})';
        // Protect search result
        self::protectArrayByRegex($string_array, $search, '', true);


        return $string_array;
    }

    private static function protectArrayByRegex(&$array, $search = '', $replace = '', $protect = true, $convert = true)
    {
        $search = '#' . $search . '#si';
        if ( ! $replace)
        {
            $replace = '\1';
        }

        $is_array = is_array($array);
        if ( ! $is_array)
        {
            $array = [$array];
        }

        foreach ($array as $key => &$string)
        {
            // only do something if string is not empty
            // or on uneven count = not yet protected
            if (trim($string) == '' || fmod($key, 2))
            {
                continue;
            }

            self::protectStringByRegex($string, $search, $replace, $protect);
        }

        if ( ! $is_array)
        {
            $array = $array[0];
        }

        if ($convert)
        {
            $array = self::protectArray($array);
        }
    }

    private static function protectArrayOutsideBetweensStrings(&$string_array, $start, $end)
    {
    }

    private static function protectArrayByTagList(&$array, &$tags)
    {
    }

    private static function protectStringByRegex(&$string, $search = '', $replace = '', $protect = true)
    {
        if (RL_RegEx::match($search, $string))
        {
            $string = $protect
                ? RL_RegEx::replace($search, self::_($replace), $string)
                : self::_(RL_RegEx::replace($search, self::_($replace, false), $string));
        }

        self::cleanProtected($string);
    }

    private static function protectArray($array)
    {
        $new_array = [];

        foreach ($array as $key => $string)
        {
            // is string already protected?
            $protect    = fmod($key, 2);
            $item_array = self::protectStringToArray($string, $protect);

            $new_array = array_merge($new_array, $item_array);
        }

        return $new_array;
    }

    private static function protectStringByTagList(&$string, &$tags)
    {
    }

    public static function _($string, $protect = 1)
    {
        return $protect
            ? self::$protect_start . $string . self::$protect_end
            : self::$protect_end . $string . self::$protect_start;
    }

    private static function cleanProtected(&$string)
    {
        while (strpos($string, self::$protect_start . self::$protect_start) !== false)
        {
            $string = str_replace(self::$protect_start . self::$protect_start, self::$protect_start, $string);
        }
        while (strpos($string, self::$protect_end . self::$protect_end) !== false)
        {
            $string = str_replace(self::$protect_end . self::$protect_end, self::$protect_end, $string);
        }
        while (strpos($string, self::$protect_end . self::$protect_start) !== false)
        {
            $string = str_replace(self::$protect_end . self::$protect_start, '', $string);
        }
    }

    private static function protectStringToArray($string, $protected = true)
    {
        if ($protected)
        {
            // If already protected, just clean string and place in an array
            self::cleanProtect($string);

            return [$string];
        }

        // Return an array with 1 or 3 items.
        // 1) first part to protector start (if found) (= unprotected)
        // 2) part between the first protector start and its matching end (= protected)
        // 3) Rest of the string (= unprotected)

        $array = [];
        // Divide sting on protector start
        $string_array = explode(self::$protect_start, $string);
        // Add first element to the string ( = even = unprotected)
        self::cleanProtect($string_array[0]);
        $array[] = $string_array[0];

        $count = count($string_array);
        if ($count < 2)
        {
            return $array;
        }

        for ($i = 1; $i < $count; $i++)
        {
            $substr        = $string_array[$i];
            $protect_count = 1;

            // Add the next string if not enough protector ends are found
            while (
                substr_count($substr, self::$protect_end) < $protect_count
                && $i < ($count - 1)
            )
            {
                $protect_count++;
                $substr .= $string_array[++$i];
            }

            // Devide sting on protector end
            $substr_array = explode(self::$protect_end, $substr);

            $protect_part = '';
            // Add as many parts to the string as there are protector starts
            for ($protect_i = 0; $protect_i < $protect_count; $protect_i++)
            {
                $protect_part .= array_shift($substr_array);
                if ( ! count($substr_array))
                {
                    break;
                }
            }

            // This part is protected (uneven)
            self::cleanProtect($protect_part);
            $array[] = $protect_part;

            // The rest of the string is unprotected (even)
            $unprotect_part = implode('', $substr_array);
            self::cleanProtect($unprotect_part);
            $array[] = $unprotect_part;
        }

        return $array;
    }

    private static function protectStringByTag(&$string, $tag_name, $tag_params)
    {
    }

    public static function cleanProtect(&$string)
    {
        $string = str_replace([self::$protect_start, self::$protect_end], '', $string);
    }
}
