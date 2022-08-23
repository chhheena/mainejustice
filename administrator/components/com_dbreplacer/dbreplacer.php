<?php
/**
 * @package         DB Replacer
 * @version         7.4.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

// Access check.
if ( ! $user->authorise('core.manage', 'com_dbreplacer'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

jimport('joomla.filesystem.file');

// return if Regular Labs Library plugin is not installed
if (
	! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
	$msg = JText::_('DBR_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
		. ' ' . JText::sprintf('DBR_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_DBREPLACER'));
	JFactory::getApplication()->enqueueMessage($msg, 'error');

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COM_DBREPLACER'))
{
	return;
}

// give notice if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
	$msg = JText::_('DBR_REGULAR_LABS_LIBRARY_NOT_ENABLED')
		. ' ' . JText::sprintf('DBR_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_DBREPLACER'));
	JFactory::getApplication()->enqueueMessage($msg, 'notice');
}

RL_Language::load('plg_system_regularlabs');

$controller = JController::getInstance('DBReplacer');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
