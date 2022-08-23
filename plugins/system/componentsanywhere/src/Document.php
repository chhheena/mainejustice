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
use Joomla\Utilities\ArrayHelper;
use RegularLabs\Library\RegEx as RL_RegEx;

class Document
{
	public static function addStylesAndScripts(&$data, $area = '', $add_styles = true, $add_scripts = true, $add_meta = true)
	{
		if ($area == 'article' && JFactory::getDocument()->getType() == 'html')
		{
			self::addStylesAndScriptsToDocument($data, $add_scripts, $add_styles, $add_meta);

			return;
		}

		self::addStylesAndScriptsInline($data, $add_scripts, $add_styles, $add_meta);
	}

	public static function placeStylesAndScripts(&$head, &$body)
	{
		if (
			strpos($head, '</head>') === false
			&& strpos($body, '<!-- CA HEAD START') === false
		)
		{
			return;
		}

		RL_RegEx::matchAll('<!-- CA HEAD START STYLES -->(.*?)<!-- CA HEAD END STYLES -->', $body, $matches);

		if ( ! empty($matches))
		{
			$styles = '';
			foreach ($matches as $match)
			{
				$styles .= $match[1];

				$body = str_replace($match[0], '', $body);
			}

			$add_before = '</head>';
			if (RL_RegEx::match('<link [^>]+templates/', $body, $add_before_match))
			{
				$add_before = $add_before_match[0];
			}

			$head = str_replace($add_before, $styles . $add_before, $head);
		}

		RL_RegEx::matchAll('<!-- CA HEAD START SCRIPTS -->(.*?)<!-- CA HEAD END SCRIPTS -->', $body, $matches, null, PREG_SET_ORDER);

		if ( ! empty($matches))
		{
			$scripts = '';
			foreach ($matches as $match)
			{
				$scripts .= $match[1];

				$body = str_replace($match[0], '', $body);
			}

			$add_before = '</head>';
			if (RL_RegEx::match('<script [^>]+templates/', $body, $add_before_match))
			{
				$add_before = $add_before_match[0];
			}

			$head = str_replace($add_before, $scripts . $add_before, $head);
		}

		self::removeDuplicatesFromHead($head, '<link[^>]*>');
		self::removeDuplicatesFromHead($head, '<style.*?</style>');
		self::removeDuplicatesFromHead($head, '<script.*?</script>');
	}

	private static function addCustomInline(&$data)
	{
		self::initDataCustom($data);

		if (empty($data->custom))
		{
			return;
		}

		$custom = is_array($data->custom)
			? implode("\n", $data->custom)
			: (string) $data->custom;

		if (empty($custom))
		{
			return;
		}

		$data->html = '<!-- CA HEAD START SCRIPTS -->' . $custom . '<!-- CA HEAD END SCRIPTS -->' . $data->html;
	}

	private static function addCustomToDocument(&$data)
	{
		$doc = JFactory::getDocument();

		self::initDataCustom($data);

		foreach ($data->custom as $content)
		{
			$doc->addCustomTag($content);
		}
	}

	private static function addScriptsInline(&$data)
	{
		self::initDataScripts($data);

		$scripts = [];

		// Generate script file links
		foreach ($data->scripts as $script => $options)
		{
			$scripts[] = self::scriptToString($script, $options) . "\n";
		}

		if (isset($data->script->{'joomla-script-options'}))
		{
			foreach ($data->script->{'joomla-script-options'} as $key => $value)
			{
				$prettyPrint = (JDEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
				$jsonOptions = json_encode($data->script->{'joomla-script-options'}, $prettyPrint);
				$jsonOptions = $jsonOptions ?: '{}';

				$scripts[] = '<script type="application/json" class="joomla-script-options new">'
					. $jsonOptions
					. '</script>';
			}

			unset($data->script->{'joomla-script-options'});
		}

		// Generate script declarations
		foreach ($data->script as $type => $content)
		{
			if ( ! is_string($content))
			{
				continue;
			}

			$scripts[] = '<script type="' . $type . '">' . "\n"
				. $content . "\n"
				. '</script>' . "\n";
		}

		if (empty($scripts))
		{
			return;
		}

		$data->html = '<!-- CA HEAD START SCRIPTS -->' . implode('', $scripts) . '<!-- CA HEAD END SCRIPTS -->' . $data->html;
	}

	private static function addScriptsToDocument(&$data)
	{
		$doc = JFactory::getDocument();

		self::initDataScripts($data);

		foreach ($data->scripts as $script => $options)
		{
			$doc->addScript($script, (array) $options->options);
		}

		if (isset($data->script->{'joomla-script-options'}))
		{
			foreach ($data->script->{'joomla-script-options'} as $key => $value)
			{
				if (is_object($value))
				{
					$value = (array) $value;
				}

				$doc->addScriptOptions($key, $value);
			}

			unset($data->script->{'joomla-script-options'});
		}

		foreach ($data->script as $type => $content)
		{
			$doc->addScriptDeclaration($content, $type);
		}
	}

	private static function addStylesAndScriptsInline(&$data, $add_styles = true, $add_scripts = true, $add_meta = true)
	{
		if ($add_styles)
		{
			self::addStylesInline($data);
		}

		if ($add_scripts)
		{
			self::addScriptsInline($data);
		}

		if ($add_meta)
		{
			self::addCustomInline($data);
		}
	}

	private static function addStylesAndScriptsToDocument(&$data, $add_styles = true, $add_scripts = true, $add_meta = true)
	{
		if ($add_styles)
		{
			self::addStylesToDocument($data);
		}

		if ($add_scripts)
		{
			self::addScriptsToDocument($data);
		}

		if ($add_meta)
		{
			self::addCustomToDocument($data);
		}
	}

	private static function addStylesInline(&$data)
	{
		self::initDataStyles($data);

		$styles = [];

		// Generate stylesheet links
		foreach ($data->styles as $style => $options)
		{
			$styles[] = self::styleToString($style, $options) . "\n";
		}

		// Generate stylesheet declarations
		foreach ($data->style as $type => $content)
		{
			$styles[] = '<style type="' . $type . '">' . "\n"
				. $content . "\n"
				. '</style>' . "\n";
		}

		if (empty($styles))
		{
			return;
		}

		$data->html = '<!-- CA HEAD START STYLES -->' . implode('', $styles) . '<!-- CA HEAD END STYLES -->' . $data->html;
	}

	private static function addStylesToDocument(&$data)
	{
		$doc = JFactory::getDocument();

		self::initDataStyles($data);

		foreach ($data->styles as $style => $options)
		{
			$doc->addStyleSheet($style, (array) $options->options);
		}

		foreach ($data->style as $type => $content)
		{
			$doc->addStyleDeclaration($content, $type);
		}
	}

	private static function initDataCustom($data)
	{
		$data->custom = ($data->custom ?? null) ?: [];
	}

	private static function initDataScripts($data)
	{
		$doc = JFactory::getDocument();

		$data->scripts = ($data->scripts ?? null) ?: [];
		$data->script  = ($data->script ?? null) ?: [];

		self::removeDuplicatesFromObject($data->scripts, $doc->_scripts);
		self::removeDuplicatesFromObject($data->script, $doc->_script, 1);
	}

	private static function initDataStyles($data)
	{
		$doc = JFactory::getDocument();

		$data->styles = ($data->styles ?? null) ?: [];
		$data->style  = ($data->style ?? null) ?: [];

		self::removeDuplicatesFromObject($data->styles, $doc->_styleSheets);
		self::removeDuplicatesFromObject($data->style, $doc->_style, 1);
	}

	private static function removeDuplicatesFromHead(&$head, $regex = '')
	{
		RL_RegEx::matchAll($regex, $head, $matches, null, PREG_PATTERN_ORDER);

		if (empty($matches))
		{
			return;
		}

		$tags = [];

		foreach ($matches[0] as $tag)
		{
			if ( ! in_array($tag, $tags))
			{
				$tags[] = $tag;
				continue;
			}

			$tag  = RL_RegEx::quote($tag);
			$head = RL_RegEx::replace('(' . $tag . '.*?)\s*' . $tag, '\1', $head);
		}
	}

	private static function removeDuplicatesFromObject(&$obj, $doc, $match_value = 0)
	{
		if (empty($obj))
		{
			return;
		}

		foreach ($obj as $key => $val)
		{
			if (isset($doc[$key]) && ( ! $match_value || $doc[$key] == $val))
			{
				unset($obj->{$key});
			}
		}
	}

	private static function scriptToString($script, $options)
	{
		$attributes = '';
		$attributes .= ! empty($options->type) ? ' type="' . $options->type . '"' : '';
		$attributes .= ! empty($options->mime) ? ' type="' . $options->mime . '"' : '';
		$attributes .= ! empty($options->defer) ? ' defer="defer"' : '';
		$attributes .= ! empty($options->async) ? ' async="async"' : '';

		return '<script src="' . $script . '"' . $attributes . '></script>';
	}

	private static function styleToString($style, $options)
	{
		$attributes = '';
		$attributes .= ! empty($options->media) ? ' media="' . $options->media . '"' : '';
		$attributes .= ! empty($options->type) ? ' type="' . $options->type . '"' : '';
		$attributes .= ! empty($options->mime) ? ' type="' . $options->mime . '"' : '';
		$attributes .= ! empty($options->attribs) ? ' ' . ArrayHelper::toString((array) $options->attribs) : '';

		return '<link rel="stylesheet" href="' . $style . '"' . $attributes . '>';
	}

}
