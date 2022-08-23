<?php
/**
 * @package         Components Anywhere
 * @version         4.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ComponentsAnywhere;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
	protected static $params  = null;
	protected static $regexes = null;

	public static function get()
	{
		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		$params = RL_Parameters::getPlugin('componentsanywhere');

		$params->tag = RL_PluginTag::clean($params->component_tag);


		self::$params = $params;

		return self::$params;
	}

	public static function getRegex($type = 'tag')
	{
		$regexes = self::getRegexes();

		return $regexes->{$type} ?? $regexes->tag;
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
				$tag_start . $params->tag,
			],
			[
				$tag_end,
			],
		];

		return $only_start_tags ? $tags[0] : $tags;
	}

	public static function setTagCharacters()
	{
		$params = self::get();

		[self::$params->tag_character_start, self::$params->tag_character_end] = explode('.', $params->tag_characters);
	}

	private static function getRegexes()
	{
		if ( ! is_null(self::$regexes))
		{
			return self::$regexes;
		}

		$params = self::get();

		// Tag character start and end
		[$tag_start, $tag_end] = Params::getTagCharacters();

		$pre        = RL_PluginTag::getRegexSurroundingTagsPre();
		$post       = RL_PluginTag::getRegexSurroundingTagsPost();
		$inside_tag = RL_PluginTag::getRegexInsideTag($tag_start, $tag_end);

		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$spaces = RL_PluginTag::getRegexSpaces();

		self::$regexes = (object) [];

		self::$regexes->tag =
			'(?<start_div>(?:'
			. $pre
			. $tag_start . 'div(?: ' . $inside_tag . ')?' . $tag_end
			. $post
			. '\s*)?)'

			. '(?<pre>' . $pre . ')'
			. $tag_start . RL_RegEx::quote($params->tag) . $spaces . '(?<id>' . $inside_tag . ')' . $tag_end
			. '(?<post>' . $post . ')'

			. '(?<end_div>(?:\s*'
			. $pre
			. $tag_start . '/div' . $tag_end
			. $post
			. ')?)';

		return self::$regexes;
	}
}
