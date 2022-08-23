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
use RegularLabs\Library\CacheNew as RL_Cache;
use RegularLabs\Library\RegEx as RL_RegEx;

class Component
{
	public static function createJsonString($html)
	{
		return json_encode((object) ['html' => $html]);
	}

	public static function get($url, $use_cache = false)
	{
		if (JFactory::getApplication()->input->get('debug_component'))
		{
			$data = Data::get($url);

			echo "\n\n<pre>=======Component===================\n";
			print_r($url);
			echo "\n==========================\n";
			print_r($data);
			echo "\n==========================</pre>\n\n";
			exit;
		}

		if ($use_cache)
		{
			$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

			$cache = (new RL_Cache(
				[
					__METHOD__,
					$url,
					JFactory::getLanguage()->getTag(),
					$user->getAuthorisedGroups(),
				]
			))->useFiles();

			$data = $cache->get();

			if ( ! empty($data))
			{
				return $cache->set($data);
			}
		}

		$data = Data::get($url);

		if ( ! empty($data))
		{
			$data = self::convertHtmlToObject($data);
		}

		if ($use_cache)
		{
			return $cache->set($data);
		}

		return $data;
	}

	public static function getObject($body = '')
	{
		$doc = JFactory::getDocument();

		return (object) [
			'script'  => self::getScriptDeclaration(),
			'scripts' => $doc->_scripts,
			'style'   => $doc->_style,
			'styles'  => $doc->_styleSheets,
			'custom'  => $doc->_custom,
			'html'    => $body,
			'token'   => JFactory::getSession()->getFormToken(),
		];
	}

	public static function render($object)
	{
		header('Content-Type: application/json');
		echo json_encode($object);
		die();
	}

	private static function convertHtmlToObject($data)
	{
		if (empty($data) || $data == '{}')
		{
			return false;
		}

		// remove possible leading encoding characters
		$data = RL_RegEx::replace('^.*?\{', '{', $data);

		$data = json_decode($data);
		if (is_null($data) || empty($data))
		{
			return false;
		}

		return $data;
	}

	private static function formatJavascriptString($string)
	{
		return str_replace(
			['\t', '\n'],
			[JFactory::getDocument()->_getTab(), JFactory::getDocument()->_getLineEnd()],
			$string
		);
	}

	private static function getScriptDeclaration()
	{
		$script         = JFactory::getDocument()->_script;
		$script_options = JFactory::getDocument()->getScriptOptions();

		if ($script_options)
		{
			$script['joomla-script-options'] = $script_options;
		}

		return $script;
	}
}
