<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class MinitekWallControllerMasonry extends JControllerLegacy
{
  function __construct()
	{
		parent::__construct();
	}

	public function getContent()
	{
		// Get input
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$jinput = $app->input;

		// Get variables
		$widget_id = $jinput->get('widget_id', '', 'INT');
		$page = $jinput->get('page', '2', 'INT');
		$grid = $jinput->get('grid', 'masonry', 'STRING');

		// Get params
		$model = $this->getModel('Masonry', 'MinitekWallModel');
		$utilities = $model->utilities;
		$masonry_params = $utilities->getMasonryParams($widget_id);
		$layout = $masonry_params['mas_layout'];
		$layout = substr($layout, strpos($layout, ":") + 1);

		// Set variables
    $jinput->set('view', 'masonry');
		$jinput->set('widget_id', $widget_id);
		$jinput->set('page', $page);

		// Set layout
		$jinput->set('layout', $layout.'_'.$grid, 'STRING');

		// Display
		parent::display();

		// Exit
		$app->close();
	}

	public function getFilters()
	{
		// Get input
		$app = JFactory::getApplication();
		$jinput = $app->input;

		// Get variables
		$widget_id = $jinput->get('widget_id', '', 'INT');
		$page = $jinput->get('page', '2', 'INT');

		// Get params
		$model = $this->getModel('Masonry', 'MinitekWallModel');
		$utilities = $model->utilities;
		$masonry_params = $utilities->getMasonryParams($widget_id);
		$layout = $masonry_params['mas_layout'];
		$layout = substr($layout, strpos($layout, ":") + 1);

		// Set variables
    $jinput->set('view', 'masonry');
		$jinput->set('widget_id', $widget_id);
		$jinput->set('page', $page);
		$jinput->set('filters', 'filters');

		// Set layout
		$jinput->set('layout', $layout.'_filters', 'STRING');

		// Display
		parent::display();

		// Exit
		$app->close();
	}
}
