<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallViewGrid extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = MinitekWallHelperUtilities::getActions();

		// Load js libraries
		JHtml::_('bootstrap.framework');
		JFactory::getDocument()->addScript(JURI::root(true).'/components/com_minitekwall/assets/js/packery.pkgd.min.js');
		JFactory::getDocument()->addScript(JURI::root(true).'/components/com_minitekwall/assets/js/draggabilly.pkgd.min.js');
		JFactory::getDocument()->addScript(JURI::root(true).'/administrator/components/com_minitekwall/assets/js/grid.js');

		if (!$this->item->get('elements'))
			$this->item->set('elements', '""');
		JFactory::getDocument()->addScriptDeclaration(
		'window.mwvars = {
			elements: '.$this->item->get('elements').'
		};'
		);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		\JToolbarHelper::title(
			\JText::_('COM_MINITEKWALL_GRID_TITLE_' . ($checkedOut ? 'VIEW_GRID' : ($isNew ? 'NEW_GRID' : 'EDIT_GRID'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($canDo->get('core.create') && $isNew)
		{
			JToolbarHelper::apply('grid.apply');
			JToolbarHelper::save('grid.save');
			JToolbarHelper::save2new('grid.save2new');
			JToolbarHelper::cancel('grid.cancel');
		}
		else
		{
			if (!$checkedOut && $canDo->get('core.edit'))
			{
				JToolbarHelper::apply('grid.apply');
				JToolbarHelper::save('grid.save');

				if ($canDo->get('core.create'))
				{
					JToolbarHelper::save2new('grid.save2new');
				}
			}

			if (!$isNew && $canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('grid.save2copy');
			}

			JToolbarHelper::cancel('grid.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
