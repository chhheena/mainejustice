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

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

if ( ! $user->authorise('core.manage', 'com_templates'))
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
	$msg = JText::_('ATP_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
		. ' ' . JText::sprintf('ATP_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDTEMPLATES'));
	JFactory::getApplication()->enqueueMessage($msg, 'error');

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COM_ADVANCEDTEMPLATES'))
{
	return;
}

// give notice if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
	$msg = JText::_('ATP_REGULAR_LABS_LIBRARY_NOT_ENABLED')
		. ' ' . JText::sprintf('ATP_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDTEMPLATES'));
	JFactory::getApplication()->enqueueMessage($msg, 'notice');
}

RL_Language::load('plg_system_regularlabs');
RL_Language::load('com_templates', JPATH_ADMINISTRATOR);

JHtml::_('behavior.tabstate');

JLoader::register('AdvancedTemplatesHelper', __DIR__ . '/helpers/templates.php');

$controller = JController::getInstance('AdvancedTemplates');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
