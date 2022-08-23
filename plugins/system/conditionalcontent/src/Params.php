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

use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
    protected static $params = null;
    protected static $regex  = null;

    public static function get()
    {
        if ( ! is_null(self::$params))
        {
            return self::$params;
        }

        $params = RL_Parameters::getPlugin('conditionalcontent');

        $params->tag_show = RL_PluginTag::clean($params->tag_show);
        $params->tag_hide = RL_PluginTag::clean($params->tag_hide);

        self::$params = $params;

        return self::$params;
    }

    public static function getRegex()
    {
        if ( ! is_null(self::$regex))
        {
            return self::$regex;
        }

        $params = self::get();

        // Tag character start and end
        [$tag_start, $tag_end] = Params::getTagCharacters();

        $pre        = RL_PluginTag::getRegexSurroundingTagsPre();
        $post       = RL_PluginTag::getRegexSurroundingTagsPost();
        $inside_tag = RL_PluginTag::getRegexInsideTag($tag_start, $tag_end);
        $spaces     = RL_PluginTag::getRegexSpaces();

        $tag_start = RL_RegEx::quote($tag_start);
        $tag_end   = RL_RegEx::quote($tag_end);

        self::$regex =
            '(?<start_pre>' . $pre . ')'
            . $tag_start . '(?<tag>' . RL_RegEx::quote($params->tag_show) . '|' . RL_RegEx::quote($params->tag_hide) . ')'
            . '(?<data>(?:(?:' . $spaces . '|<)' . $inside_tag . ')?)' . $tag_end
            . '(?<start_post>' . $post . ')'

            . '(?<content>.*?)'

            . '(?<end_pre>' . $pre . ')'
            . $tag_start . '/\2' . $tag_end
            . '(?<end_post>' . $post . ')';

        return self::$regex;
    }

    public static function getTagCharacters()
    {
        $params = self::get();

        if ( ! isset($params->tag_character_start))
        {
            self::setTagCharacters();
        }

        return [$params->tag_character_start, $params->tag_character_end];
    }

    public static function getTags($only_start_tags = false)
    {
        $params = self::get();

        [$tag_start, $tag_end] = self::getTagCharacters();

        $tags = [
            [
                $tag_start . $params->tag_show,
                $tag_start . $params->tag_hide,
            ],
            [
                $tag_start . '/' . $params->tag_show . $tag_end,
                $tag_start . '/' . $params->tag_hide . $tag_end,
            ],
        ];

        return $only_start_tags ? $tags[0] : $tags;
    }

    public static function setTagCharacters()
    {
        $params = self::get();

        [self::$params->tag_character_start, self::$params->tag_character_end] = explode('.', $params->tag_characters);
    }
}
