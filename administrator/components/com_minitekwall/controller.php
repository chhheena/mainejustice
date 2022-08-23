<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

class MinitekWallController extends JControllerLegacy
{
	protected $default_view = 'dashboard';

	public function display($cachable = false, $urlparams = false)
	{
		parent::display();

		return $this;
	}

	public function checkForUpdate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$type = $input->get('type', 'auto');
		$params = JComponentHelper::getParams('com_minitekwall');
		$version_check = $params->get('version_check', 1);

		// Don't allow auto if version checking is disabled
		if ($type == 'auto' && !$version_check)
		{
			$app->close();
		}

		$input->set('view', 'dashboard', 'STRING');
		$input->set('layout', 'update', 'STRING');

		// Display
		parent::display();

		// Exit
		$app->close();
	}
}
