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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Replace
{
	static $message       = '';
	static $protect_end   = '<!-- END: CA_PROTECT -->';
	static $protect_start = '<!-- START: CA_PROTECT -->';

	public static function processComponents(&$string, $area = 'article', $context = '', $article = null)
	{
		$params = Params::get();


		// Check if tags are in the text snippet used for the search component
		if (strpos($context, 'com_search.') === 0)
		{
			$limit = explode('.', $context, 2);
			$limit = (int) array_pop($limit);

			$string_check = substr($string, 0, $limit);

			if ( ! RL_String::contains($string_check, Params::getTags(true)))
			{
				return;
			}
		}


		if ( ! RL_String::contains($string, Params::getTags(true)))
		{
			return;
		}

		self::replace($string, $area);
	}

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

		$params = Params::get();

		// allow in component?
		if (RL_Protect::isRestrictedComponent($params->disabled_components ?? [], $area))
		{

			Protect::_($string);

			self::removeAll($string, $area);

			RL_Protect::unprotect($string);

			return true;
		}

		Protect::_($string);

		// COMPONENT
		if (RL_Document::isFeed())
		{
			$string = RL_RegEx::replace('(<item[^>]*>)', '\1<!-- START: CODA_COMPONENT -->', $string);
			$string = str_replace('</item>', '<!-- END: CODA_COMPONENT --></item>', $string);
		}

		if (strpos($string, '<!-- START: CODA_COMPONENT -->') === false)
		{
			Area::tag($string, 'component');
		}

		self::$message = '';

		$components = Area::get($string, 'component');

		foreach ($components as $component)
		{
			if (strpos($string, $component[0]) === false)
			{
				continue;
			}

			self::processComponents($component[1], 'components');
			$string = str_replace($component[0], $component[1], $string);
		}

		// EVERYWHERE
		self::processComponents($string, 'other');

		RL_Protect::unprotect($string);

		return true;
	}

	private static function getDivTags($data)
	{
		[$tag_start, $tag_end] = Params::getTagCharacters(true);

		return RL_PluginTag::getDivTags($data['start_div'], $data['end_div'], $tag_start, $tag_end);
	}

	private static function processComponent($tag, $area = '')
	{
		$params = Params::get();

		$url = ltrim(RL_String::html_entity_decoder(trim($tag->url)), '/');

		$pagination_stuff = ['p', 'page', 'limitstart', 'start', 'filter', 'filter-search'];
		$full_url         = $url;
		foreach ($pagination_stuff as $key)
		{
			if ( ! isset($_GET[$key]))
			{
				continue;
			}

			$full_url .= (strpos($url, '?') === false) ? '?' : '&';
			$full_url .= $key . '=' . $_GET[$key];
		}

		if ($tag->force_itemid)
		{
			$full_url = RL_RegEx::replace(
				'((?:\?|&(?:amp;)?)Itemid)=[0-9]+',
				'\1=' . JFactory::getApplication()->input->get('Itemid'),
				$full_url
			);

			$full_url .= (strpos($full_url, '?') === false) ? '?' : '&';
			$full_url .= 'Itemid=' . JFactory::getApplication()->input->get('Itemid');
		}

		$data = Component::get($full_url, $tag->caching);

		if ( ! $data)
		{
			if ($params->place_comments)
			{
				return Protect::getMessageCommentTag(JText::_('CA_OUTPUT_REMOVED_INVALID'));
			}

			return '';
		}

		$add_styles_scripts = $tag->add_styles_scripts ?? $tag->add_scripts_styles ?? $params->add_scripts_styles;

		if ($add_styles_scripts)
		{
			$add_styles  = $tag->add_styles ?? $params->add_styles;
			$add_scripts = $tag->add_scripts ?? $params->add_scripts;
			$add_meta    = $tag->add_meta ?? $params->add_meta;

			Document::addStylesAndScripts($data, $area, $add_styles, $add_scripts, $add_meta);
		}

		$uri = JUri::getInstance();

		// Remove tmpl and rendercomponent parameters that have possibly been added to urls by the component
		self::removeFromUrls($data->html, '(?:tmpl=' . $params->tmpl . '&(?:amp;)?)?rendercomponent=1');

		if ($params->force_remove_tmpl)
		{
			self::removeFromUrls($data->html, 'tmpl=' . $params->tmpl);
		}

		// Replace the form token with the current correct one
		if ( ! empty($data->token))
		{
			$data->html = str_replace(
				$data->token,
				JFactory::getSession()->getFormToken(),
				$data->html
			);
		}

		// Replace the return values in urls
		$data->html = RL_RegEx::replace(
			'(\?|&(?:amp;)?)return=(?:[a-z0-9=]+)',
			'\1return=' . base64_encode($uri->toString()),
			$data->html
		);

		if ($tag->force_itemid)
		{
			// Replace Itemid
			$data->html = RL_RegEx::replace(
				'(\?|&(?:amp;)?)Itemid=[0-9]+',
				'\1Itemid=' . JFactory::getApplication()->input->get('Itemid'),
				$data->html
			);
		}

		if ( ! $tag->keepurls)
		{
			$path = $uri->getPath();
			$path .= (strpos($path, '?') === false) ? '?' : '&';

			// Replace urls
			$data->html = RL_RegEx::replace(
				'\/' . RL_RegEx::quote([
					$url,
					JRoute::_($url),
					JRoute::_($url, false),
				]) . '(/?[\?\&\"\'])',
				$path . '\2',
				$data->html
			);

			// Also get non-sef matches
			$url_regex  = str_replace('&', '&(?:amp;)?', str_replace('index.php', '', $url));
			$data->html = RL_RegEx::replace('"[^"]+' . $url_regex . '("|&)', '"' . $path . '\1', $data->html);
		}

		return $data->html;
	}

	private static function processMatch(&$string, &$data, $area = 'article')
	{
		$params = Params::get();

		if ( ! empty(self::$message))
		{
			$html = '';

			if ($params->place_comments)
			{
				$html = Protect::getMessageCommentTag(self::$message);
			}

			$string = str_replace($data[0], $html, $string);

			return true;
		}

		$id = trim($data['id']);

		// Handle multiple attribute syntaxes
		$id = str_replace(
			['|cache', '|forceitemid', '|keepurl', '|keepurlss'],
			['|caching', '|force_itemid', '|keepurls', '|keepurls'],
			$id
		);

		$known_boolean_keys = [
			'caching', 'force_itemid', 'keepurls',
		];

		$tag = RL_PluginTag::getAttributesFromString($id, 'url', $known_boolean_keys);

		if ( ! empty($tag->params))
		{
			foreach ($tag->params as $param)
			{
				$tag->{$param} = 1;
			}
		}
		unset($tag->params);

		$tag->force_itemid = $tag->force_itemid ?? $params->force_itemid;
		$tag->keepurls     = $tag->keepurls ?? $params->keepurls;
		$tag->caching      = $tag->caching ?? $params->caching;

		$html = self::processComponent($tag, $area);

		[$start_div, $end_div] = self::getDivTags($data);

		$tags = RL_Html::cleanSurroundingTags([
			'start_div_pre'  => $start_div['pre'],
			'start_div_post' => $start_div['post'],
			'pre'            => $data['pre'],
			'post'           => $data['post'],
			'end_div_pre'    => $end_div['pre'],
			'end_div_post'   => $end_div['post'],
		]);

		$html = $tags['start_div_pre'] . $start_div['tag'] . $tags['start_div_post']
			. $tags['pre'] . $html . $tags['post']
			. $tags['end_div_pre'] . $end_div['tag'] . $tags['end_div_post'];

		if ($params->place_comments)
		{
			$html = Protect::wrapInCommentTags($html);
		}

		$string = str_replace($data[0], $html, $string);
		unset($data);

		return true;
	}

	private static function removeAll(&$string, $area = 'article')
	{
		self::$message = JText::_('CA_OUTPUT_REMOVED_NOT_ENABLED');
		self::processComponents($string, $area);
	}

	private static function removeFromUrls(&$html, $search = '')
	{
		// Replace the <search term>&<something else> cases
		$html = RL_RegEx::replace('(\?|&(?:amp;)?)' . $search . '&(?:amp;)?', '\1', $html);
		// Replace the <search term> cases
		$html = RL_RegEx::replace('(?:\?|&(?:amp;)?)' . $search, '', $html);
	}

	private static function replace(&$string, $area = 'article')
	{
		[$start_tags, $end_tags] = Params::getTags();

		[$pre_string, $string, $post_string] = RL_Html::getContentContainingSearches(
			$string,
			$start_tags,
			$end_tags
		);

		if ($string == '' || ! RL_String::contains($string, Params::getTags(true)))
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		$regex = Params::getRegex();

		$matches  = [];
		$protects = [];

		RL_RegEx::matchAll($regex, $string, $matches);

		if (empty($matches))
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		foreach ($matches as $match)
		{
			if (strpos($string, $match[0]) === false)
			{
				continue;
			}

			if (self::processMatch($string, $match, $area))
			{
				continue;
			}

			$protected  = self::$protect_start . base64_encode($match[0]) . self::$protect_end;
			$string     = str_replace($match[0], $protected, $string);
			$protects[] = [$match[0], $protected];
		}

		unset($matches);

		foreach ($protects as $protect)
		{
			if (strpos($string, $protect[1]) === false)
			{
				continue;
			}

			$string = str_replace($protect[1], $protect[0], $string);
		}

		$string = $pre_string . $string . $post_string;
	}
}
