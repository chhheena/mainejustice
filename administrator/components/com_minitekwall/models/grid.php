<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class MinitekWallModelGrid extends JModelAdmin
{
	protected $text_prefix = 'COM_MINITEKWALL';

	public $typeAlias = 'com_minitekwall.grid';

	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return false;
			}
			$user = JFactory::getUser();

			return $user->authorise('core.delete', 'com_minitekwall');
		}

		return false;
	}

	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing record.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_minitekwall.grid.' . (int) $record->id);
		}
		// Default to component settings if record unknown.
		else
		{
			return parent::canEditState('com_minitekwall');
		}
	}

	protected function prepareTable($table)
	{
		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
	}

	public function getTable($type = 'Grid', $prefix = 'MinitekWallTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			return $item;
		}
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_minitekwall.grid', 'grid', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}

		// Determine correct permissions to check.
		if ($this->getState('grid.id'))
		{
			$id = $this->getState('grid.id');
		}

		$user = JFactory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_minitekwall.grid.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_minitekwall')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_minitekwall.edit.grid.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Group, Access) in edit form if those have been selected in filters
			if ($this->getState('grid.id') == 0)
			{
				$filters = (array) $app->getUserState('com_minitekwall.grids.filter');
				$data->set(
					'state',
					$app->input->getInt(
						'state',
						((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
					)
				);
			}
		}

		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_minitekwall.grid', $data);

		return $data;
	}

	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$filter = JFilterInput::getInstance();

		if (parent::save($data))
		{
			return true;
		}

		return false;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'minitekwall')
	{
		parent::preprocessForm($form, $data, $group);
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_minitekwall');
	}
}
