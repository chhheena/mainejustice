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

use Joomla\CMS\Factory as JFactory;

class Document
{
	public static function getScript()
	{
		$script = JFactory::getDocument()->_script;
		$script = $script['text/javascript'] ?? '';

		$script_options = JFactory::getDocument()->getScriptOptions();

		if (empty($script_options['plg_editor_tinymce']['tinyMCE']))
		{
			return $script;
		}

		foreach ($script_options['plg_editor_tinymce']['tinyMCE'] as $type)
		{
			if (empty($type['joomlaExtButtons']['script']))
			{
				continue;
			}

			$script .= "\n" . implode("\n", $type['joomlaExtButtons']['script']);
		}

		return $script;
	}
}
