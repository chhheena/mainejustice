<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper as JApplicationHelper;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\AdminModel as JModelAdmin;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Table\Table as JTable;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper as JArrayHelper;
use RegularLabs\Library\Date as RL_Date;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\StringHelper as RL_String;

/**
 * Template style model.
 */
class AdvancedTemplatesModelStyle extends JModelAdmin
{
	/**
	 * The help screen key for the module.
	 *
	 * @var        string
	 * @since   1.6
	 */
	protected $helpKey = 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT';

	/**
	 * The help screen base URL for the module.
	 *
	 * @var        string
	 * @since   1.6
	 */
	protected $helpURL;

	/**
	 * Item cache.
	 *
	 * @var    array
	 * @since  1.6
	 */
	private $_cache = [];

	/**
	 * Method to delete rows.
	 *
	 * @param array &$pks An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 * @throws  Exception
	 */
	public function delete(&$pks)
	{
		$pks        = (array) $pks;
		$user       = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$table      = $this->getTable();
		$dispatcher = JEventDispatcher::getInstance();
		$context    = 'com_advancedtemplates.' . $this->name;
		$db         = $this->getDbo();

		JPluginHelper::importPlugin($this->events_map['delete']);

		// Iterate the items to delete each one.
		foreach ($pks as $pk)
		{
			if ( ! $table->load($pk))
			{
				$this->setError($table->getError());

				return false;
			}

			// Access checks.
			if ( ! $user->authorise('core.delete', 'com_templates'))
			{
				throw new Exception(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
			}

			// You should not delete a default style
			if ($table->home != '0')
			{
				JError::raiseWarning(SOME_ERROR_NUMBER, JText::_('COM_TEMPLATES_STYLE_CANNOT_DELETE_DEFAULT_STYLE'));

				return false;
			}

			// Trigger the before delete event.
			$result = $dispatcher->trigger($this->event_before_delete, [$context, $table]);

			if (in_array(false, $result, true) || ! $table->delete($pk))
			{
				$this->setError($table->getError());

				return false;
			}

			$query = $db->getQuery(true)
				->delete('#__advancedtemplates')
				->where('styleid=' . (int) $pk);
			$db->setQuery($query);
			$db->execute();

			// delete asset
			$query->clear()
				->delete('#__assets')
				->where('name = ' . $db->quote('com_advancedtemplates.style.' . (int) $pk));
			$db->setQuery($query);
			$db->execute();

			// Trigger the after delete event.
			$dispatcher->trigger($this->event_after_delete, [$context, $table]);
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param type   $type   The table type to instantiate
	 * @param string $prefix A prefix for the table class name. Optional.
	 * @param array  $config Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 */
	public function getTable($type = 'Style', $prefix = 'AdvancedTemplatesTable', $config = [])
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Custom clean cache method
	 *
	 * @param string  $group     The cache group
	 * @param integer $client_id The ID of the client
	 *
	 * @return  void
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_templates');
		parent::cleanCache('com_advancedtemplates');
		parent::cleanCache('_system');
	}

	/**
	 * Method to duplicate styles.
	 *
	 * @param array &$pks An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws    Exception
	 */
	public function duplicate(&$pks)
	{
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		// Access checks.
		if ( ! $user->authorise('core.create', 'com_templates'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$dispatcher = JEventDispatcher::getInstance();
		$context    = 'com_advancedtemplates.' . $this->name;

		// Include the plugins for the save events.
		JPluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ( ! $table->load($pk, true))
			{
				throw new Exception($table->getError());
			}

			// Reset the id to create a new record.
			$table->id = 0;

			// Reset the home (don't want dupes of that field).
			$table->home = 0;

			// Alter the title.
			$m            = null;
			$table->title = $this->generateNewTitle(null, null, $table->title);

			if ( ! $table->check())
			{
				throw new Exception($table->getError());
			}

			// Trigger the before save event.
			$result = $dispatcher->trigger($this->event_before_save, [$context, &$table, true]);

			if (in_array(false, $result, true) || ! $table->store())
			{
				throw new Exception($table->getError());
			}

			// Trigger the after save event.
			$dispatcher->trigger($this->event_after_save, [$context, &$table, true]);
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param integer $category_id The id of the category.
	 * @param string  $alias       The alias.
	 * @param string  $title       The title.
	 *
	 * @return  string  New title.
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title
		$table = $this->getTable();

		while ($table->load(['title' => $title]))
		{
			$title = RL_String::increment($title);
		}

		return $title;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param array   $data     An optional array of data for the form to interogate.
	 * @param boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		// The folder and element vars are passed when saving the form.
		if (empty($data))
		{
			$item     = $this->getItem();
			$clientId = $item->client_id;
			$template = $item->template;
		}
		else
		{
			$clientId = JArrayHelper::getValue($data, 'client_id');
			$template = JArrayHelper::getValue($data, 'template');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.client_id', $clientId);
		$this->setState('item.template', $template);

		// Get the form.
		$form = $this->loadForm('com_advancedtemplates.style', 'style', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if ( ! $this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('home', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('home', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = ( ! empty($pk)) ? $pk : (int) $this->getState('style.id');

		if (isset($this->_cache[$pk]))
		{
			return $this->_cache[$pk];
		}

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());

			return false;
		}

		// Convert to the JObject before adding other data.
		$properties        = $table->getProperties(1);
		$this->_cache[$pk] = JArrayHelper::toObject($properties, 'JObject');

		// Convert the params field to an array.
		$registry = new Registry;
		$registry->loadString($table->params);
		$this->_cache[$pk]->params = $registry->toArray();

		// Advanced parameters
		$table_adv = JTable::getInstance('AdvancedTemplates', 'AdvancedTemplatesTable');
		$table_adv->load($pk);

		$this->_cache[$pk]->asset_id = $table_adv->asset_id;

		// Convert the params field to an array.
		$this->_cache[$pk]->advancedparams = json_decode($table_adv->params, true);
		if (is_null($this->_cache[$pk]->advancedparams))
		{
			$this->_cache[$pk]->advancedparams = [];
		}

		$this->_cache[$pk]->advancedparams = $this->initAssignments($pk, $this->_cache[$pk]);

		// Get the template XML.
		$client = JApplicationHelper::getClientInfo($table->client_id);
		$path   = JPath::clean($client->path . '/templates/' . $table->template . '/templateDetails.xml');

		$this->_cache[$pk]->xml = null;

		if (file_exists($path))
		{
			$this->_cache[$pk]->xml = simplexml_load_file($path);
		}

		return $this->_cache[$pk];
	}

	/**
	 * Method to get and save the style core menu assignments
	 *
	 * @param int $id The style id.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function initAssignments($id, &$style)
	{
		if ( ! $id)
		{
			$id = JFactory::getApplication()->input->getInt('id');
		}

		if ( ! isset($style->advancedparams))
		{
			$style->advancedparams = [
				'assignto_menuitems'           => 0,
				'assignto_menuitems_selection' => [],
			];
		}

		if ( ! isset($style->advancedparams['assignto_menuitems'])
			&& ! empty($id)
		)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('m.id')
				->from('#__menu as m')
				->where('m.template_style_id = ' . (int) $id);
			$db->setQuery($query);
			$selection = $db->loadColumn();

			if ( ! empty($selection))
			{
				$style->advancedparams['assignto_menuitems']           = 1;
				$style->advancedparams['assignto_menuitems_selection'] = $selection;
			}
		}

		AdvancedTemplatesModelStyle::saveAdvancedParams($style->advancedparams, $id);

		return $style->advancedparams;
	}

	/**
	 * Method to save the advanced parameters.
	 *
	 * @param array $data The form data.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function saveAdvancedParams($data, $id = 0)
	{
		if ( ! $id)
		{
			$id = JFactory::getApplication()->input->getInt('id');
		}

		if ( ! $id)
		{
			return true;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_advancedtemplates/tables/advancedtemplates.php';
		$table_adv          = JTable::getInstance('AdvancedTemplates', 'AdvancedTemplatesTable');
		$table_adv->styleid = $id;

		if ($id && ! $table_adv->load($id))
		{
			$db = $table_adv->getDbo();
			$db->insertObject($table_adv->getTableName(), $table_adv, $table_adv->getKeyName());
		}

		if (isset($data['rules']))
		{
			$table_adv->_title = $data['title'];
			$table_adv->setRules($data['rules']);
		}

		$table_adv->params = json_encode($data);

		// Check the row
		$table_adv->check();

		try
		{
			// Store the data.
			$table_adv->store();
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage(), 500);
		}

		return true;
	}

	/**
	 * Get the necessary data to load an item help screen.
	 *
	 * @return  object  An object with key, url, and local properties for loading the item help screen.
	 */
	public function getHelp()
	{
		return (object) ['key' => $this->helpKey, 'url' => $this->helpURL];
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		// Detect disabled extension
		$extension = JTable::getInstance('Extension');

		if ($extension->load(['enabled' => 0, 'type' => 'template', 'element' => $data['template'], 'client_id' => $data['client_id']]))
		{
			$this->setError(JText::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));

			return false;
		}

		$advancedparams = JFactory::getApplication()->input->get('advancedparams', [], 'array');

		$app        = JFactory::getApplication();
		$dispatcher = JEventDispatcher::getInstance();
		$table      = $this->getTable();
		$pk         = ( ! empty($data['id'])) ? $data['id'] : (int) $this->getState('style.id');
		$isNew      = true;
		$context    = 'com_advancedtemplates.' . $this->name;

		$data['title'] = trim($data['title']);

		// Include the extension plugins for the save events.
		JPluginHelper::importPlugin($this->events_map['save']);

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if ($app->input->get('task') == 'save2copy')
		{
			$data['title']    = $this->generateNewTitle(null, null, $data['title']);
			$data['home']     = 0;
			$data['assigned'] = '';
		}

		// correct the publish date details
		if (isset($advancedparams['assignto_date_publish_up']))
		{
			RL_Date::applyTimezone($advancedparams['assignto_date_publish_up']);
		}

		if (isset($advancedparams['assignto_date_publish_down']))
		{
			RL_Date::applyTimezone($advancedparams['assignto_date_publish_down']);
		}

		if (isset($advancedparams['assignto_articles_date_date']))
		{
			RL_Date::applyTimezone($advancedparams['assignto_articles_date_date']);
		}

		if (isset($advancedparams['assignto_articles_date_from']))
		{
			RL_Date::applyTimezone($advancedparams['assignto_articles_date_from']);
		}

		if (isset($advancedparams['assignto_articles_date_to']))
		{
			RL_Date::applyTimezone($advancedparams['assignto_articles_date_to']);
		}

		// Bind the data.
		if ( ! $table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if ( ! $table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before save event.
		$result = $dispatcher->trigger($this->event_before_save, [$context, &$table, $isNew]);

		// Store the data.
		if (in_array(false, $result, true) || ! $table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$table_adv = JTable::getInstance('AdvancedTemplates', 'AdvancedTemplatesTable');

		$table_adv->styleid = $table->id;
		if ($table_adv->styleid && ! $table_adv->load($table_adv->styleid))
		{
			$db = $table_adv->getDbo();
			$db->insertObject($table_adv->getTableName(), $table_adv, $table_adv->getKeyName());
		}

		$table_adv->params = json_encode($advancedparams);

		// Check the row
		$table_adv->check();

		// Store the row
		if ( ! $table_adv->store())
		{
			$this->setError($table_adv->getError());
		}

		$db = $this->getDbo();

		// Remove unused assets entry (uses core com_templates rules)
		$query = $db->getQuery(true)
			->delete('#__assets')
			->where('name = ' . $db->quote('com_advancedtemplates.style.' . (int) $table->id));
		$db->setQuery($query);
		$db->execute();

		//
		// Process the menu link mappings.
		//
		$data['assigned'] = [];
		$reverse          = 0;
		if (isset($advancedparams['assignto_menuitems']) && isset($advancedparams['assignto_menuitems_selection']))
		{
			$data['assigned'] = $advancedparams['assignto_menuitems_selection'];
			if ($advancedparams['assignto_menuitems'] == 0)
			{
				$data['assigned'] = [];
			}
			else if ($advancedparams['assignto_menuitems'] == 2)
			{
				$reverse = 1;
			}
		}

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		if ($user->authorise('core.edit', 'com_menus') && $table->client_id == 0)
		{
			$n    = 0;
			$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

			if ( ! empty($data['assigned']) && is_array($data['assigned']))
			{
				$assigned = JArrayHelper::toInteger($data['assigned']);
				$assigned = array_diff($assigned, [0]);

				if ( ! empty($assigned))
				{
					// Update the mapping for menu items that this style IS assigned to.
					if ($reverse)
					{
						$assigned[0] = '!' . $assigned[0];
					}

					$query = $db->getQuery(true)
						->update('#__menu')
						->set('template_style_id = ' . (int) $table->id)
						->where('id' . RL_DB::in($assigned))
						->where('template_style_id != ' . (int) $table->id)
						->where('checked_out IN (0,' . (int) $user->id . ')');

					$db->setQuery($query);
					$db->execute();
					$n += $db->getAffectedRows();
				}
			}

			// Remove style mappings for menu items this style is NOT assigned to.
			// If unassigned then all existing maps will be removed.
			$query = $db->getQuery(true)
				->update('#__menu')
				->set('template_style_id = 0');

			if ( ! empty($data['assigned']) && is_array($data['assigned']))
			{
				$assigned = JArrayHelper::toInteger($data['assigned']);
				$assigned = array_diff($assigned, [0]);

				if ( ! empty($assigned))
				{
					if ( ! $reverse)
					{
						$assigned[0] = '!' . $assigned[0];
					}

					$query->where('id' . RL_DB::in($assigned));
				}
			}

			$query->where('template_style_id = ' . (int) $table->id)
				->where('checked_out IN (0,' . (int) $user->id . ')');
			$db->setQuery($query);
			$db->execute();

			$n += $db->getAffectedRows();

			if ($n > 0)
			{
				$app->enqueueMessage(JText::plural('COM_TEMPLATES_MENU_CHANGED', $n));
			}
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the after save event.
		$dispatcher->trigger($this->event_after_save, [$context, &$table, $isNew]);

		$this->setState('style.id', $table->id);

		return true;
	}

	/**
	 * Method to set a template style as home.
	 *
	 * @param integer $id The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws    Exception
	 */
	public function setHome($id = 0)
	{
		$user    = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$db      = $this->getDbo();
		$context = 'com_advancedtemplates.' . $this->name;

		// Access checks.
		if ( ! $user->authorise('core.edit.state', 'com_templates'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$style = JTable::getInstance('Style', 'AdvancedTemplatesTable');

		if ( ! $style->load((int) $id))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}

		// Detect disabled extension
		$extension = JTable::getInstance('Extension');

		if ($extension->load(['enabled' => 0, 'type' => 'template', 'element' => $style->template, 'client_id' => $style->client_id]))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));
		}

		// Reset the home fields for the client_id.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'0\'' .
			' WHERE client_id = ' . (int) $style->client_id .
			' AND home = \'1\''
		);
		$db->execute();

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'1\'' .
			' WHERE id = ' . (int) $id
		);
		$db->execute();

		JEventDispatcher::getInstance()->trigger('onAfterTemplateStyleSetHome', [$context, $id]);

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to set color of template styles.
	 *
	 * @param array  &$pks   An array of primary key IDs.
	 * @param string  $color RGB color
	 *
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function setcolor(&$pks, $color)
	{
		// Set the variables
		$db         = $this->getDbo();
		$user       = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$table      = $this->getTable();
		$table_adv  = JTable::getInstance('AdvancedTemplates', 'AdvancedTemplatesTable');
		$dispatcher = JEventDispatcher::getInstance();
		$context    = 'com_advancedtemplates.' . $this->name;

		foreach ($pks as $pk)
		{
			if ( ! $user->authorise('core.edit', 'com_templates'))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}

			if ( ! $table_adv->load($pk))
			{
				$table_adv->styleid = $pk;
				$db->insertObject($table_adv->getTableName(), $table_adv, $table_adv->getKeyName());
			}

			if ( ! $table_adv->load($pk, true))
			{
				continue;
			}

			$params = json_decode($table_adv->params);
			if (is_null($params))
			{
				$params = (object) [];
			}

			$params->color = strtolower($color);

			$table_adv->params = json_encode($params);

			if ( ! $table_adv->check() || ! $table_adv->store())
			{
				$this->setError($table_adv->getError());

				return false;
			}

			$table->load($pk);
			// Trigger the after save event.
			$dispatcher->trigger($this->event_after_save, [$context, &$table, false]);
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to unset a template style as default for a language.
	 *
	 * @param integer $id The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws    Exception
	 */
	public function unsetHome($id = 0)
	{
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if ( ! $user->authorise('core.edit.state', 'com_templates'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		// Lookup the client_id.
		$db->setQuery(
			'SELECT client_id, home' .
			' FROM #__template_styles' .
			' WHERE id = ' . (int) $id
		);
		$style = $db->loadObject();

		if ( ! is_numeric($style->client_id))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}
		elseif ($style->home == '1')
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));
		}

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'0\'' .
			' WHERE id = ' . (int) $id
		);
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_advancedtemplates.edit.style.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_advancedtemplates.style', $data);

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @note    Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState('style.id', $pk);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_advancedtemplates');
		$this->setState('params', $params);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param JForm  $form  A JForm object.
	 * @param mixed  $data  The data expected for the form.
	 * @param string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$clientId = $this->getState('item.client_id');
		$template = $this->getState('item.template');
		$lang     = JFactory::getLanguage();
		$client   = JApplicationHelper::getClientInfo($clientId);

		if ( ! $form->loadFile('style_' . $client->name, true))
		{
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		jimport('joomla.filesystem.path');

		$formFile = JPath::clean($client->path . '/templates/' . $template . '/templateDetails.xml');

		// Load the core and/or local language file(s).
		$lang->load('tpl_' . $template, $client->path, null, false, true)
		|| $lang->load('tpl_' . $template, $client->path . '/templates/' . $template, null, false, true);

		if (file_exists($formFile))
		{
			// Get the template form.
			if ( ! $form->loadFile($formFile, false, '//config'))
			{
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Disable home field if it is default style

		if ((is_array($data) && array_key_exists('home', $data) && $data['home'] == '1')
			|| ((is_object($data) && isset($data->home) && $data->home == '1'))
		)
		{
			$form->setFieldAttribute('home', 'readonly', 'true');
		}

		// Attempt to load the xml file.
		if ( ! $xml = simplexml_load_file($formFile))
		{
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		// Get the help data from the XML file if present.
		$help = $xml->xpath('/extension/help');

		if ( ! empty($help))
		{
			$helpKey = trim((string) $help[0]['key']);
			$helpURL = trim((string) $help[0]['url']);

			$this->helpKey = $helpKey ?: $this->helpKey;
			$this->helpURL = $helpURL ?: $this->helpURL;
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}
}
