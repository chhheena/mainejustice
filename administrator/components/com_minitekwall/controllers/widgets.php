<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);

class MinitekWallControllerWidgets extends JControllerAdmin
{
	protected $text_prefix = 'COM_MINITEKWALL';

	public function getModel($name = 'Widget', $prefix = 'MinitekWallModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function deleteCroppedImages()
	{
		// Delete images folder
		jimport('joomla.filesystem.folder');
		JSession::checkToken('request') or jexit('Invalid token');
		$app = JFactory::getApplication();

		$tmppath = JPATH_SITE.DS.'images'.DS.'mnwallimages'.DS;
		if (file_exists($tmppath))
		{
			JFolder::delete($tmppath);
			$message = JText::_('COM_MINITEKWALL_CROPPED_IMAGES_DELETED');
			$link = JRoute::_('index.php?option=com_minitekwall&view=widgets');
			$app->redirect(str_replace('&amp;', '&', $link), $message, 'message');
		}
		else
		{
			$message = JText::_('COM_MINITEKWALL_CROPPED_IMAGES_NOT_FOUND');
			$link = JRoute::_('index.php?option=com_minitekwall&view=widgets');
			$app->redirect(str_replace('&amp;', '&', $link), $message, 'notice');
		}
	}
}
