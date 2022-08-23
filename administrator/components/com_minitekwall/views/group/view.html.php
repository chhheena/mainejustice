<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallViewGroup extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;
	protected $canDo;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = MinitekWallHelperUtilities::getActions();

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
			\JText::_('COM_MINITEKWALL_GROUP_TITLE_' . ($checkedOut ? 'VIEW_GROUP' : ($isNew ? 'NEW_GROUP' : 'EDIT_GROUP'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($canDo->get('core.create') && $isNew)
		{
			JToolbarHelper::apply('group.apply');
			JToolbarHelper::save('group.save');
			JToolbarHelper::save2new('group.save2new');
			JToolbarHelper::cancel('group.cancel');
		}
		else
		{
			if (!$checkedOut && $canDo->get('core.edit'))
			{
				JToolbarHelper::apply('group.apply');
				JToolbarHelper::save('group.save');

				if ($canDo->get('core.create'))
				{
					JToolbarHelper::save2new('group.save2new');
				}
			}

			JToolbarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
