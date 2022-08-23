<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallViewGroups extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$utilities = new MinitekWallHelperUtilities();
		$utilities->addSubmenu('groups');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = MinitekWallHelperUtilities::getActions();
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_MINITEKWALL_GROUPS_TITLE'), 'folder');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('group.add');
		}

		if (($canDo->get('core.edit')))
		{
			JToolbarHelper::editList('group.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('groups.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('groups.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('groups.archive');
			JToolbarHelper::checkin('groups.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('groups.trash');
		}

		if ($user->authorise('core.admin', 'com_minitekwall') || $user->authorise('core.options', 'com_minitekwall'))
		{
			JToolbarHelper::preferences('com_minitekwall');
		}

		JHtmlSidebar::setAction('index.php?option=com_minitekwall&view=groups');
	}
}
