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

defined('_JEXEC') or die;

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPlugin as RL_EditorButtonPlugin;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Plugin\System\ContentTemplater\Items as CT_Items;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/EditorButtonPlugin.php')
)
{
	return;
}

if ( ! is_file(JPATH_PLUGINS . '/system/contenttemplater/vendor/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
	RL_Extension::disable('contenttemplater', 'plugin', 'editors-xtd');

	return;
}

require_once JPATH_PLUGINS . '/system/contenttemplater/vendor/autoload.php';

if (true)
{
	class PlgButtonContentTemplater extends RL_EditorButtonPlugin
	{
		var $main_type            = 'component';
		var $check_installed      = ['component', 'plugin'];
		var $require_core_auth    = false;
		var $enable_on_acymailing = true;

		public function extraChecks($params)
		{
			$items = CT_Items::get('button');

			if (empty($items))
			{
				return false;
			}

			return true;
		}
	}
}
