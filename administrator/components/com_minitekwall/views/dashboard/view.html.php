<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallViewDashboard extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->utilities = new MinitekWallHelperUtilities();

		// Skip if view == update
		if (JFactory::getApplication()->input->get('view') != 'update')
		{
			// Load dashboard.js
			JHtml::_('bootstrap.framework');
			JFactory::getDocument()->addScript(JURI::root(true).'/administrator/components/com_minitekwall/assets/js/dashboard.js');

			// Check for Minitek authentication plugin
			$this->authEnabled = $this->utilities->getMinitekAuthPlugin();

			$this->addToolbar();

			parent::display($tpl);
		}
	}

	protected function addToolbar()
	{
		$user = JFactory::getUser();
		JToolbarHelper::title(JText::_('COM_MINITEKWALL_DASHBOARD_TITLE'), '');

		if ($user->authorise('core.admin', 'com_minitekwall') || $user->authorise('core.options', 'com_minitekwall'))
		{
			JToolbarHelper::preferences('com_minitekwall');
		}
	}
}
