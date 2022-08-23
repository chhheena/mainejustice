<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ContentTemplater;

defined('_JEXEC') or die;

use RegularLabs\Library\RegEx as RL_RegEx;

class Editors
{
	public static function get($buffer)
	{
		if (strpos($buffer, '<textarea') === false)
		{
			return false;
		}

		// Found TinyMCE based editor
		if (RL_RegEx::matchAll(
			'<textarea\s[^>]*id="([^"]*)"[^>]*mce_editable',
			$buffer,
			$matches,
			null,
			PREG_PATTERN_ORDER
		))
		{
			return array_unique($matches[1]);
		}

		// Editor is TinyMCE and using javascript to place buttons
		if (strpos($buffer, 'tinyMCE') !== false)
		{
			$buffer = Document::getScript();
		}

		// Found Content Templater button
		if (RL_RegEx::matchAll(
			'rl_ct_button-([^ "\']+)',
			$buffer,
			$matches,
			null,
			PREG_PATTERN_ORDER
		))
		{
			return array_unique($matches[1]);
		}

		return false;
	}
}
