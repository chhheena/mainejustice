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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use RegularLabs\Library\Alias as RL_Alias;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Date as RL_Date;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Variables
{
    static $contact = null;
    static $profile = null;
    static $user    = null;

    public static function replacePost(&$string, $sub_splitter = '')
    {
        if (empty($string))
        {
            return;
        }

        if ( ! $sub_splitter)
        {

            return;
        }

        $parts = explode($sub_splitter, $string);
        foreach ($parts as $i => $part)
        {
            if ($i % 2 == 0)
            {
                continue;
            }

            self::replacePost($part);
            $parts[$i] = $part;
        }

        $string = implode('', $parts);
    }

    private static function replaceTagByType(&$string, $type)
    {
    }

    // single [[tag:...]] style tag on single line

    private static function replaceDoubleTagByType(&$string, $type)
    {
    }

    // double [[tag]]...[[/tag]] style tag on multiple lines

    private static function replaceMatchByType($type, &$string, $match)
    {
    }

    private static function getArticleValue($key)
    {
    }

    private static function geUserValue($key)
    {
        if ($key == 'password')
        {
            return '';
        }

        $user = self::getUser();

        if ($user->guest)
        {
            return '';
        }

        if (isset($user->{$key}))
        {
            return $user->{$key};
        }

        $contact = self::getContact();

        if (isset($contact->{$key}))
        {
            return $contact->{$key};
        }

        $profile = self::getProfile();

        if (isset($profile->{$key}))
        {

            return $profile->{$key};
        }

        return '';
    }

    private static function getDateValue($value)
    {
        return self::getDateFromFormat($value);
    }

    private static function getRandomValue($value)
    {
        $values = RL_Array::toArray($value);

        foreach ($values as $i => $value)
        {
            if (RL_RegEx::match('^([0-9]+)-([0-9]+)$', trim($value), $range))
            {
                $values[$i] = self::getRandomValueFromRange($range);
            }
        }

        return $values[rand(0, count($values) - 1)];
    }

    private static function getEscapeValue($value)
    {
        return addslashes($value);
    }

    private static function getLowercaseValue($value)
    {
        return strtolower($value);
    }

    private static function getUppercaseValue($value)
    {
        return strtoupper($value);
    }

    private static function getUser()
    {
        if ( ! is_null(self::$user))
        {
            return self::$user;
        }

        self::$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        self::flattenObject(self::$user);

        return self::$user;
    }

    private static function getContact()
    {
        if (self::$contact)
        {
            return self::$contact;
        }

        $db = JFactory::getDbo();

        $query = 'SHOW TABLES LIKE ' . $db->quote($db->getPrefix() . 'contact_details');
        $db->setQuery($query);

        $has_contact_table = $db->loadResult();
        if ( ! $has_contact_table)
        {
            self::$contact = (object) [
                'x' => '',
            ];

            return self::$contact;
        }

        $query = $db->getQuery(true)
            ->select('c.*')
            ->from('#__contact_details as c')
            ->where('c.user_id = ' . (int) self::$user->id);
        $db->setQuery($query);
        self::$contact = $db->loadObject();

        if ( ! self::$contact)
        {
            self::$contact = (object) [
                'x' => '',
            ];

            return self::$contact;
        }

        self::flattenObject(self::$contact);

        return self::$contact;
    }

    private static function getProfile()
    {
        if (self::$profile)
        {
            return self::$profile;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('p.profile_key, p.profile_value')
            ->from('#__user_profiles as p')
            ->where('p.user_id = ' . (int) self::$user->id);
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $profile    = (object) [];
        $profile->x = '';

        foreach ($rows as $row)
        {
            $data = json_decode($row->profile_value);
            if (is_null($data))
            {
                $data = (object) [];
            }

            $profile->{substr($row->profile_key, 8)} = $data;
        }

        self::$profile = $profile;

        return self::$profile;
    }

    private static function getDateFromFormat($date)
    {
        if ($date && strpos($date, '%') !== false)
        {
            $date = RL_Date::strftimeToDateFormat($date);
        }

        $date = str_replace('[TH]', '[--==--]', $date);

        $date = JHtml::_('date', 'now', $date);

        self::replaceThIndDate($date, '[--==--]');

        return $date;
    }

    private static function getRandomValueFromRange($range)
    {
        return rand((int) $range[1], (int) $range[2]);
    }

    private static function flattenObject(&$object, &$flat = null)
    {
        $flat = (object) [];

        if (empty($object))
        {
            return;
        }

        foreach ($object as $propkey => $property)
        {
            if (empty($property))
            {
                continue;
            }

            if (is_string($property))
            {
                $property = (string) $property;
            }

            if (is_string($property) && $property[0] == '{')
            {
                $property = json_decode($property);
            }

            if (is_string($property))
            {
                self::setParam($flat, $propkey, $property);
                continue;
            }

            if ( ! is_object($property) && ! is_array($property))
            {
                continue;
            }

            foreach ($property as $key => $value)
            {
                self::setParam($flat, $key, $value);
            }
        }
    }

    private static function replaceThIndDate(&$date, $th = '[TH]')
    {
        if (strpos($date, $th) === false)
        {
            return;
        }

        RL_RegEx::matchAll('([0-9]+)' . RL_RegEx::quote($th), $date, $date_matches);

        if (empty($date_matches))
        {
            $date = str_replace($th, 'th', $date);

            return;
        }

        foreach ($date_matches as $date_match)
        {
            switch ($date_match[1])
            {
                case 1:
                case 21:
                case 31:
                    $suffix = 'st';
                    break;
                case 2:
                case 22:
                case 32:
                    $suffix = 'nd';
                    break;
                case 3:
                case 23:
                    $suffix = 'rd';
                    break;
                default:
                    $suffix = 'th';
                    break;
            }

            $date = RL_String::replaceOnce($date_match[0], $date_match[1] . $suffix, $date);
        }

        $date = str_replace($th, 'th', $date);
    }

    private static function setParam(&$object, $key, $value)
    {
        if (isset($object->{$key})
            || is_numeric($key)
            || is_object($value)
            || is_array($value)
        )
        {
            return;
        }

        $object->{$key} = $value;
    }

    public static function replacePre(&$string, $article = null)
    {
    }
}
