<?php
/**
 * @package         Conditional Content
 * @version         4.0.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ConditionalContent;

defined('_JEXEC') or die;

use RegularLabs\Library\Conditions as RL_Conditions;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Replace
{
    static $assignment_types = [
        'menuitems',
        'homepage',
        'date',
        'accesslevels',
        'usergrouplevels',
        'languages',
        'devices',
    ];

    public static function replaceTags(&$string, $area = 'article', $context = '')
    {
        if ( ! is_string($string) || $string == '')
        {
            return false;
        }

        if ( ! RL_String::contains($string, Params::getTags(true)))
        {
            return false;
        }

        // Check if tags are in the text snippet used for the search component
        if (strpos($context, 'com_search.') === 0)
        {
            $limit = explode('.', $context, 2);
            $limit = (int) array_pop($limit);

            $string_check = substr($string, 0, $limit);

            if ( ! RL_String::contains($string_check, Params::getTags(true)))
            {
                return false;
            }
        }

        $params = Params::get();
        $regex  = Params::getRegex();

        // allow in component?
        if (RL_Protect::isRestrictedComponent($params->disabled_components ?? [], $area))
        {

            Protect::_($string);

            $string = RL_RegEx::replace($regex, '\2', $string);

            RL_Protect::unprotect($string);

            return true;
        }

        Protect::_($string);

        [$start_tags, $end_tags] = Params::getTags();

        [$pre_string, $string, $post_string] = RL_Html::getContentContainingSearches(
            $string,
            $start_tags,
            $end_tags
        );

        RL_RegEx::matchAll($regex, $string, $matches);

        foreach ($matches as $match)
        {
            self::replaceTag($string, $match);
        }

        $string = $pre_string . $string . $post_string;

        RL_Protect::unprotect($string);

        return true;
    }

    private static function getContent($type, $has_access, $string, $else_string = '')
    {
        $params = Params::get();
        [$tag_start, $tag_end] = Params::getTagCharacters();

        $else_tag = $tag_start
            . ($type == $params->tag_hide ? $params->tag_hide : $params->tag_show) . '-else'
            . $tag_end;

        if (strpos($string, $else_tag) !== false)
        {
            [$string, $else_string] = explode($else_tag, $string, 2);
        }

        if ( ! $has_access)
        {
            return $else_string;
        }

        return $string;
    }

    private static function getTagValues($string)
    {
        // Get the values from the tag
        return RL_PluginTag::getAttributesFromString($string, null, [], false);
    }

    private static function hasAccess($attributes)
    {
        if (empty($attributes))
        {
            return true;
        }


        $conditions = RL_Conditions::getConditionsFromTagAttributes($attributes, self::$assignment_types);

        $matching_method = strtolower($attributes->matching_method ?? '');
        $matching_method = in_array($matching_method, ['any', 'or']) ? 'or' : 'and';

        return RL_Conditions::pass($conditions, $matching_method);
    }

    private static function replaceTag(&$string, $match)
    {
        $params = Params::get();

        $attributes = self::getTagValues($match['data']);

        $trim = $attributes->trim ?? $params->trim;
        unset($attributes->trim);

        $has_access = self::hasAccess($attributes);
        $has_access = $match['tag'] == $params->tag_hide ? ! $has_access : $has_access;

        $content = self::getContent($match['tag'], $has_access, $match['content'], ($attributes->else ?? ''));

        if ($trim)
        {
            $tags = RL_Html::cleanSurroundingTags([
                'start_pre'  => $match['start_pre'],
                'start_post' => $match['start_post'],
            ], ['p', 'span', 'div']);

            $match = array_merge($match, $tags);

            $tags = RL_Html::cleanSurroundingTags([
                'end_pre'  => $match['end_pre'],
                'end_post' => $match['end_post'],
            ], ['p', 'span', 'div']);

            $match = array_merge($match, $tags);

            $tags = RL_Html::cleanSurroundingTags([
                'start_pre' => $match['start_pre'],
                'end_post'  => $match['end_post'],
            ], ['p', 'span', 'div']);

            $match = array_merge($match, $tags);
        }

        if ($params->place_comments)
        {
            $content = Protect::wrapInCommentTags($content);
        }

        $replace = $match['start_pre'] . $match['start_post'] . $content . $match['end_pre'] . $match['end_post'];

        $string = str_replace($match[0], $replace, $string);
    }
}
