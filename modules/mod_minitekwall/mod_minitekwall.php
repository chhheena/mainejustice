<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek.gr. All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

$jinput = JFactory::getApplication()->input;

// Exit if in article edit form
if ($jinput->get('option') == 'com_content' && $jinput->get('view') == 'form' && $jinput->get('layout') == 'edit')
	return;

jimport('joomla.application.component.helper');
$componentParams = JComponentHelper::getParams('com_minitekwall');
$document = JFactory::getDocument();

if ($componentParams->get('load_fontawesome', 1))
{
	$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css');
}

$widget_id = $params->get('widget_id');

// Get widget type
$db = JFactory::getDBO();
$query = ' SELECT * '
	. ' FROM '. $db->quoteName('#__minitek_wall_widgets') . ' '
	. ' WHERE '.$db->quoteName('id').' = ' . $db->Quote($widget_id);
$db->setQuery($query);
$widget_type = $db->loadObject()->type_id;

// Load page
$option = $jinput->getCmd('option', NULL);
$view = $jinput->getCmd('view', NULL);
$layout = $jinput->getCmd('layout', NULL);
$task = $jinput->getCmd('task', NULL);
$jinput->set('option', 'com_minitekwall');
$jinput->set('view', $widget_type);
$jinput->set('widget_id', $widget_id);

// Load language
$lang = JFactory::getLanguage();
$lang->load('com_minitekwall', JPATH_SITE);

if (!class_exists('MinitekWallController'))
{
	require_once (JPATH_SITE.DS.'components'.DS.'com_minitekwall'.DS.'controller.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_minitekwall'.DS.'models'.DS.'masonry.php');

	$scroller_model = JPATH_SITE.DS.'components'.DS.'com_minitekwall'.DS.'models'.DS.'scroller.php';
	if (file_exists($scroller_model))
		require_once ($scroller_model);
}

// Load controller
$controller = new MinitekWallController();
$controller->setProperties(array(
	'basePath' => JPATH_SITE .DS. 'components' .DS. 'com_minitekwall',
	'paths' => array(
		'view' => array(
			JPATH_SITE .DS. 'components' .DS. 'com_minitekwall' .DS. 'views'
		),
		'model' => array(
			JPATH_SITE .DS. 'components' .DS. 'com_minitekwall' .DS. 'models'
		)
	)
));
$controller->execute('display');

if ($option != null)
{
	$jinput->set('option', $option);
}

if ($view != null)
{
	$jinput->set('view', $view);
}

if ($layout != null)
{
	$jinput->set('layout', $layout);
}

if ($task != null)
{
	$jinput->set('task', $task);
}
