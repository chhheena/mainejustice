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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ContentTemplater\Content;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = JFactory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/SystemPlugin.php')
)
{
	JFactory::getLanguage()->load('plg_system_contenttemplater', __DIR__);
	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('CT_EXTENSION_CAN_NOT_FUNCTION', JText::_('CONTENTTEMPLATER'))
		. ' ' . JText::_('CT_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
		'error'
	);

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'CONTENTTEMPLATER'))
{
	RL_Extension::disable('contenttemplater', 'plugin');

	RL_Document::adminError(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('CONTENTTEMPLATER'))
	);

	return;
}

if (true)
{
	class PlgSystemContentTemplater extends RL_SystemPlugin
	{
		public $_lang_prefix     = 'CT';
		public $_enable_in_admin = true;
		public $_jversion        = 3;

		protected function extraChecks()
		{
			// return if component is not installed
			if ( ! file_exists(JPATH_ADMINISTRATOR . '/components/com_contenttemplater/models/list.php'))
			{
				return false;
			}

			// return if editor button is not installed
			if ( ! file_exists(JPATH_PLUGINS . '/editors-xtd/contenttemplater/contenttemplater.php'))
			{
				return false;
			}

			return parent::extraChecks();
		}

		protected function changeDocumentBuffer(&$buffer)
		{
			// only in html
			if ( ! RL_Document::isHtml())
			{
				return false;
			}

			Content::place($buffer);


			return true;
		}
	}
}
