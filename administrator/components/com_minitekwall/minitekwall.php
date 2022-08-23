<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Check component access
if (!JFactory::getUser()->authorise('core.manage', 'com_minitekwall'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include utilities helper
JLoader::register('MinitekWallHelperUtilities', JPATH_COMPONENT_ADMINISTRATOR. '/helpers/utilities.php');

$controller	= JControllerLegacy::getInstance('MinitekWall');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

$document = JFactory::getDocument();

// Add stylesheet
$document->addStyleSheet(JURI::root(true).'/administrator/components/com_minitekwall/assets/css/style.css?v=3.9.2');
$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css');

// Clear state variables if view != widget
$app = JFactory::getApplication();
$view = $app->input->get('view');
if ($view !== 'widget')
{
	$app->setUserState('com_minitekwall.type_id', '');
	$app->setUserState('com_minitekwall.source_id', '');
}
