<?php

/**
 * @title				Minitek Wall
 * @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
 * @license   		GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

class MinitekWallModelWidget extends JModelAdmin
{
	protected $text_prefix = 'COM_MINITEKWALL';

	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			if ($record->state != -2) {
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

		// Check for existing widget.
		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', 'com_minitekwall.widget.' . (int) $record->id);
		}
		// Default to component settings if widget unknown.
		else {
			return parent::canEditState('com_minitekwall');
		}
	}

	public function getTable($type = 'Widget', $prefix = 'MinitekWallTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			$db = JFactory::getDBO();

			// Convert the masonry_params to an array.
			$item->masonry_params = '';
			if ($item->get('type_id') == 'masonry') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets');
				$query->where($db->quoteName('id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$masonry_params = $db->loadObject()->masonry_params;

				$registry = new JRegistry;
				$registry->loadString($masonry_params);
				$item->masonry_params = $registry->toArray();
			}

			// Convert the scroller_params to an array.
			$item->scroller_params = '';
			if ($item->get('type_id') == 'scroller') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets');
				$query->where($db->quoteName('id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$scroller_params = $db->loadObject()->scroller_params;

				$registry = new JRegistry;
				$registry->loadString($scroller_params);
				$item->scroller_params = $registry->toArray();
			}

			// Get joomla_source from widgets_source table and convert to an array.
			$item->joomla_source = '';
			if ($item->get('source_id')	== 'joomla') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$joomla_source = $db->loadObject()->joomla_source;

				$registry = new JRegistry;
				$registry->loadString($joomla_source);
				$item->joomla_source = $registry->toArray();
			}

			// Get custom_source from widgets_source table and convert to an array.
			$item->custom_source = '';
			if ($item->get('source_id')	== 'custom') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$custom_source = $db->loadObject()->custom_source;

				$registry = new JRegistry;
				$registry->loadString($custom_source);
				$item->custom_source = $registry->toArray();
			}

			// Get k2_source from widgets_source table and convert to an array.
			$item->k2_source = '';
			if ($item->get('source_id')	== 'k2') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$k2_source = $db->loadObject()->k2_source;

				$registry = new JRegistry;
				$registry->loadString($k2_source);
				$item->k2_source = $registry->toArray();
			}

			// Get virtuemart_source from widgets_source table and convert to an array.
			$item->virtuemart_source = '';
			if ($item->get('source_id')	== 'virtuemart') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$virtuemart_source = $db->loadObject()->virtuemart_source;

				$registry = new JRegistry;
				$registry->loadString($virtuemart_source);
				$item->virtuemart_source = $registry->toArray();
			}

			// Get jomsocial_source from widgets_source table and convert to an array.
			$item->jomsocial_source = '';
			if ($item->get('source_id')	== 'jomsocial') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$jomsocial_source = $db->loadObject()->jomsocial_source;

				$registry = new JRegistry;
				$registry->loadString($jomsocial_source);
				$item->jomsocial_source = $registry->toArray();
			}

			// Get easyblog_source from widgets_source table and convert to an array.
			$item->easyblog_source = '';
			if ($item->get('source_id')	== 'easyblog') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$easyblog_source = $db->loadObject()->easyblog_source;

				$registry = new JRegistry;
				$registry->loadString($easyblog_source);
				$item->easyblog_source = $registry->toArray();
			}

			// Get easysocial_source from widgets_source table and convert to an array.
			$item->easysocial_source = '';
			if ($item->get('source_id')	== 'easysocial') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$easysocial_source = $db->loadObject()->easysocial_source;

				$registry = new JRegistry;
				$registry->loadString($easysocial_source);
				$item->easysocial_source = $registry->toArray();
			}

			// Get folder_source from widgets_source table and convert to an array.
			$item->folder_source = '';
			if ($item->get('source_id')	== 'folder') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$folder_source = $db->loadObject()->folder_source;

				$registry = new JRegistry;
				$registry->loadString($folder_source);
				$item->folder_source = $registry->toArray();
			}

			// Get rss_source from widgets_source table and convert to an array.
			$item->rss_source = '';
			if ($item->get('source_id')	== 'rss') {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__minitek_wall_widgets_source');
				$query->where($db->quoteName('widget_id') . ' = ' . (int) $item->id);
				$db->setQuery($query);
				$rss_source = $db->loadObject()->rss_source;

				$registry = new JRegistry;
				$registry->loadString($rss_source);
				$item->rss_source = $registry->toArray();
			}
		}

		return $item;
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_minitekwall.widget', 'widget', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id')) {
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else {
			$id = $jinput->get('id', 0);
		}

		$user = JFactory::getUser();

		// Check for existing widget.
		// Modify the form based on Edit State access controls.
		if (
			$id != 0 && (!$user->authorise('core.edit.state', 'com_minitekwall'))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_minitekwall'))
		) {
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a widget you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	public function getMasonryForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_minitekwall.masonry', 'masonry', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getScrollerForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_minitekwall.scroller', 'scroller', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_minitekwall.edit.widget.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		$this->preprocessData('com_minitekwall.widget', $data);

		return $data;
	}

	public function save($data)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$formData = new JRegistry($input->get('jform', '', 'array'));

		$masonry_params = $formData->get('masonry_params', 0);

		// Masonry params
		if ($masonry_params && is_object($masonry_params)) {
			$registry = new JRegistry;
			$registry->loadObject($masonry_params);
			$data['masonry_params'] = (string) $registry; // Saves to table
		}

		$scroller_params = $formData->get('scroller_params', 0);

		// Scroller params
		if ($scroller_params && is_object($scroller_params)) {
			$registry = new JRegistry;
			$registry->loadObject($scroller_params);
			$data['scroller_params'] = (string) $registry; // Saves to table
		}

		if (parent::save($data)) {
			return true;
		}

		return false;
	}

	protected function prepareTable($table)
	{
		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
	}

	public function createModule($id, $position)
	{
		$db = JFactory::getDbo();

		// Get widget name
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__minitek_wall_widgets AS s');
		$query->where('s.id = ' . (int) $id);

		// Setup the query
		$db->setQuery($query);

		$widget = $db->loadObject();
		if (!$widget) {
			return false;
		} else {
			$widget_name = $widget->name;
		}

		// Create module
		$widget_params = '{"widget_id":" ' . $id . '"}';
		$query = $db->getQuery(true);
		$columns = array('title', 'position', 'module', 'access', 'params', 'language');
		$values = array($db->quote($widget_name), $db->quote($position), $db->quote('mod_minitekwall'), $db->quote('1'), $db->quote($widget_params), $db->quote('*'));

		$query
			->insert($db->quoteName('#__modules'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$db->execute();
		$module_id = $db->insertid();

		// Handle db error
		if ($db->getErrorMsg()) {
			return false;
		} else {
			return $module_id;
		}
	}
}
