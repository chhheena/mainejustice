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

// import Joomla controller library
jimport('joomla.application.component.controller');

// Add libraries prefix
JLoader::registerPrefix('MinitekWallLib', JPATH_SITE .DS. 'components' .DS. 'com_minitekwall' .DS. 'libraries');

class MinitekWallController extends JControllerLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

  function display($cachable = false, $urlparams = false)
	{
		if (!JFactory::getApplication()->input->get('view', 'masonry'))
		{
			$error = JText::_('COM_MINITEKWALL_VIEW_NOT_FOUND');
      JError::raiseError(403, $error);
    }

		if (JFactory::getApplication()->input->get('view', 'masonry') && !JFactory::getApplication()->input->get('widget_id', '', 'INT'))
		{
			$error = JText::_('COM_MINITEKWALL_WIDGET_NOT_FOUND');
      JError::raiseError(403, $error);
    }

    parent::display();
  }
}
