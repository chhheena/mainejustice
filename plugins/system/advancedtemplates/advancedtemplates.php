<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\AdvancedTemplates\Document;

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
	JFactory::getLanguage()->load('plg_system_advancedtemplates', __DIR__);
	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('ATP_EXTENSION_CAN_NOT_FUNCTION', JText::_('ADVANCEDTEMPLATEMANAGER'))
		. ' ' . JText::_('ATP_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
		'error'
	);

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'ADVANCEDTEMPLATEMANAGER'))
{
	RL_Extension::disable('advancedtemplates', 'plugin');

	RL_Document::adminError(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('ADVANCEDTEMPLATEMANAGER'))
	);

	return;
}

if (true)
{
	class PlgSystemAdvancedTemplates extends RL_SystemPlugin
	{
		public $_title           = 'ADVANCEDTEMPLATEMANAGER';
		public $_lang_prefix     = 'ATP';
		public $_page_types      = ['html'];
		public $_enable_in_admin = true;
		public $_jversion        = 3;

		protected function extraChecks()
		{
			if ( ! RL_Protect::isComponentInstalled('advancedtemplates'))
			{
				return false;
			}

			return true;
			//return parent::extraChecks();
		}

		protected function handleOnContentPrepareForm(JForm $form, $data)
		{
			if ( ! $this->_is_admin)
			{
				return true;
			}

			return Document::changeMenuItemForm($form);
		}

		protected function handleOnAfterRoute()
		{
			if ( ! RL_Document::isClient('site'))
			{
				return;
			}

			Document::setTemplate();
		}

		protected function changeFinalHtmlOutput(&$html)
		{
			if ( ! $this->_is_admin)
			{
				return false;
			}

			return Document::replaceLinks($html);
		}
	}
}
