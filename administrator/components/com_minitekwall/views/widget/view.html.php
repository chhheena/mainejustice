<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallViewWidget extends JViewLegacy
{
	protected $form;
	protected $masonryform;
	protected $scrollerform;
	protected $item;
	protected $state;
	protected $canDo;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->masonryform = $this->get('MasonryForm');
		$this->scrollerform	= $this->get('ScrollerForm');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = MinitekWallHelperUtilities::getActions();

		$this->app = JFactory::getApplication();
		$this->type_id = $this->app->getUserState( 'com_minitekwall.type_id', '' ) ? $this->app->getUserState( 'com_minitekwall.type_id', '' ) : $this->item->type_id;
		$this->source_id = $this->app->getUserState( 'com_minitekwall.source_id', '' ) ? $this->app->getUserState( 'com_minitekwall.source_id', '' ) : $this->item->source_id;

		// Load widget.js
		JHtml::_('bootstrap.framework');
		JFactory::getDocument()->addScript(JURI::root(true).'/administrator/components/com_minitekwall/assets/js/widget.js');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Check if module is installed
		$utilities = new MinitekWallHelperUtilities();
		$this->checkModuleIsInstalled = $utilities->checkModuleIsInstalled();

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
			\JText::_('COM_MINITEKWALL_WIDGET_TITLE_' . ($checkedOut ? 'VIEW_WIDGET' : ($isNew ? 'NEW_WIDGET' : 'EDIT_WIDGET'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($canDo->get('core.create') && $isNew)
		{
			if ($this->source_id && $this->app->input->get('page') != 'source')
			{
				JToolbarHelper::apply('widget.apply');
				JToolbarHelper::save('widget.save');
				JToolbarHelper::save2new('widget.save2new');
			}

			JToolbarHelper::cancel('widget.cancel');
		}
		else
		{
			if (!$checkedOut && $canDo->get('core.edit'))
			{
				JToolbarHelper::apply('widget.apply');
				JToolbarHelper::save('widget.save');

				if ($canDo->get('core.create'))
				{
					JToolbarHelper::save2new('widget.save2new');
				}
			}

			if (!$isNew && $canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('widget.save2copy');
			}

			JToolbarHelper::cancel('widget.cancel', 'JTOOLBAR_CLOSE');
		}

		// Publish in Module
		if ($canDo->get('core.create') && !$isNew && $this->app->input->get('page') != 'type' && $this->app->input->get('page') != 'source')
		{
			JToolbarHelper::modal('createModule', 'icon-save', \JText::_('COM_MINITEKWALL_WIDGET_TOOLBAR_PUBLISH_IN_MODULE'));
		}
	}
}
