<?php
/**
* @title		Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class MinitekWallModelItem extends JModelAdmin
{
	protected $text_prefix = 'COM_MINITEKWALL';

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
			return $user->authorise('core.edit.state', 'com_minitekwall');
		}
		// Default to component settings if record unknown.
		else
		{
			return parent::canEditState('com_minitekwall');
		}
	}

	protected function prepareTable($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();

		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Reorder the items within the group so the new item is first
		if (empty($table->id))
		{
			$table->reorder('groupid = ' . (int) $table->groupid . ' AND state >= 0');
		}
	}

	public function getTable($type = 'Item', $prefix = 'MinitekWallTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the images field to an array.
			$registry = new Registry;
			$registry->loadString($item->images);
			$item->images = $registry->toArray();

			// Convert the urls field to an array.
			$registry = new Registry;
			$registry->loadString($item->urls);
			$item->urls = $registry->toArray();

			$item->description = trim($item->description);
		}

		return $item;
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_minitekwall.item', 'item', array('control' => 'jform', 'load_data' => $loadData));

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
		if ($this->getState('item.id'))
		{
			$id = $this->getState('item.id');

			// Existing record. Can only edit in selected groups.
			$form->setFieldAttribute('groupid', 'action', 'core.edit');

			// Existing record. Can only edit own item in selected groups.
			$form->setFieldAttribute('groupid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected groups.
			$form->setFieldAttribute('groupid', 'action', 'core.create');
		}

		$user = JFactory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_minitekwall'))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_minitekwall')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_minitekwall.edit.item.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Group, Access) in edit form if those have been selected in filters
			if ($this->getState('item.id') == 0)
			{
				$filters = (array) $app->getUserState('com_minitekwall.items.filter');
				$data->set(
					'state',
					$app->input->getInt(
						'state',
						((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
					)
				);
				$data->set('groupid', $app->input->getInt('groupid', (!empty($filters['group_id']) ? $filters['group_id'] : null)));
				$data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access'))));
			}
		}

		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_minitekwall.item', $data);

		return $data;
	}

	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$filter = JFilterInput::getInstance();

		if (isset($data['created_by_alias']))
		{
			$data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
		}

		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new Registry;
			$registry->loadArray($data['images']);
			$data['images'] = (string) $registry;
		}

		if (isset($data['urls']) && is_array($data['urls']))
		{
			foreach ($data['urls'] as $i => $url)
			{
				if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc'))
				{
					$data['urls'][$i] = JStringPunycode::urlToPunycode($url);
				}
			}

			$registry = new Registry;
			$registry->loadArray($data['urls']);
			$data['urls'] = (string) $registry;
		}
		
		if (parent::save($data))
		{
			return true;
		}

		return false;
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'groupid = '.(int)$table->groupid;

		return $condition;
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
