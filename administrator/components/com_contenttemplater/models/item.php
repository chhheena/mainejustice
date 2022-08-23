<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\AdminModel as JModelAdmin;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Table\Table as JTable;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Date as RL_Date;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Plugin\System\ContentTemplater\Helper as CT_Helper;

jimport('joomla.application.component.modeladmin');

/**
 * Item Model
 */
class ContentTemplaterModelItem extends JModelAdmin
{

	/**
	 * @var        string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'RL';

	/**
	 * Constructor.
	 *
	 * @param array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 */
	public function __construct()
	{
		// Load plugin parameters

		$this->_config = RL_Parameters::getComponent('contenttemplater');

		parent::__construct();
	}

	/**
	 * Method to activate list.
	 *
	 * @param array     An array of item ids.
	 * @param string    The new URL to set for the contenttemplater.
	 * @param string    A comment for the contenttemplater list.
	 *
	 * @return    boolean    Returns true on success, false on failure.
	 */
	public function activate(&$pks, $name)
	{
		// Initialise variables.
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$db   = $this->getDbo();

		// Sanitize the ids.
		$pks = ( array ) $pks;
		JArrayHelper::toInteger($pks);

		// Access checks.
		if ( ! $user->authorise('core.admin', 'com_contenttemplater'))
		{
			$pks = [];
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

			return false;
		}

		if ( ! empty($pks))
		{
			// Update the item rows.
			$db->setQuery(
				'UPDATE `#__contenttemplater`' .
				' SET `name` = ' . $db->quote($name) . ', `published` = 1' .
				' WHERE `id` IN ( ' . implode(',', $pks) . ' )'
			);
			$db->execute();

			// Check for a database error.
			$error = $this->_db->getErrorMsg();
			if ($error)
			{
				$this->setError($error);

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to copy an item
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function copy($id)
	{
		$item = $this->getItem($id);

		unset($item->_errors);
		$item->id        = 0;
		$item->published = 0;
		$item->name      = JText::sprintf('RL_COPY_OF', $item->name);

		$item = $this->validate(null, (array) $item);

		return ($this->save($item));
	}

	/**
	 * Method to get the record form.
	 *
	 * @param array   $data     Data for the form.
	 * @param boolean $loadData True if the form is to load its own data ( default case ), false if not.
	 *
	 * @return   JForm    A JForm object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_contenttemplater.item',
			'item',
			[
				'control'   => 'jform',
				'load_data' => $loadData,
			]
		);
		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if ($this->canEditState((object ) $data) != true)
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null, $getform = false, $getdefaults = false, $group = false)
	{
		// Initialise variables.
		$pk    = ( ! empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item       = JArrayHelper::toObject($properties, 'JObject');

		$isini  = ((substr($item->params, 0, 1) != '{') && (substr($item->params, -1, 1) != '}'));
		$params = RL_Parameters::getObjectFromRegistry($item->params, JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml');

		$item->fields = (object) [];

		foreach ($params as $key => $val)
		{
			if ( ! isset($item->{$key}) && ! is_object($val))
			{
				$item->{$key} = $val;
			}
		}

		unset($item->params);

		if ($isini)
		{
			foreach ($item as $key => $val)
			{
				if (is_string($val) && $key != 'content')
				{
					$item->{$key} = stripslashes($val);
				}
			}
		}

		if ($getform)
		{
			$xmlfile = JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml';
			$params  = new JForm('jform', ['control' => 'jform']);
			$params->loadFile($xmlfile, 1, '//config');
			$params->bind($item);
			$item->form = $params;
		}

		if ($group)
		{
			$assignments = $this->removeSettings($item, ['assignto_']);
			$params      = $this->removeSettings($item, ['content', 'jform_', 'customfield']);

			$item = $this->removeSettings($item, ['content', 'assignto_', 'jform_', 'customfield'], false);

			$item->assignments = $assignments;
			$item->params      = $params;
		}

		if ($getdefaults)
		{
			$item->defaults      = RL_Parameters::getObjectFromRegistry('', JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml');
			$item->form_defaults = RL_Parameters::getObjectFromRegistry('', JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml', 'form_default');

			$item->defaults      = $this->removeSettings($item->defaults);
			$item->form_defaults = $this->removeSettings($item->form_defaults);

			// set default category
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id')
				->from('#__categories')
				->where('parent_id = 1')
				->where('level = 1')
				->where('extension = ' . $db->quote('com_content'))
				->order('lft ASC')
				->setLimit(1);
			$db->setQuery($query);
			$item->form_defaults->jform_catid = $db->loadResult();
		}

		return $item;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param type      The table type to instantiate
	 * @param string    A prefix for the table class name. Optional.
	 * @param array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Item', $prefix = 'ContentTemplaterTable', $config = [])
	{
		JTable::addIncludePath(dirname(__FILE__, 2) . '/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$task   = JFactory::getApplication()->input->get('task');
		$params = json_decode($data['params'], true);

		if (is_null($params))
		{
			$params = [];
		}

		// correct the publish date details
		if (isset($params['assignto_date_publish_up']))
		{
			RL_Date::applyTimezone($params['assignto_date_publish_up']);
		}

		if (isset($params['assignto_date_publish_down']))
		{
			RL_Date::applyTimezone($params['assignto_date_publish_down']);
		}

		if ($task == 'save2copy')
		{
			$data['published'] = 0;
		}

		$this->incrementName($data['name'], $data['id']);

		$data['params'] = json_encode($params);

		return parent::save($data);
	}

	/**
	 * Method to validate form data.
	 */
	public function validate($form, $data, $group = null)
	{
		// Check for valid name
		if (empty($data['name']))
		{
			$this->setError(JText::_('CT_THE_ITEM_MUST_HAVE_A_NAME'));

			return 0;
		}

		$newdata = [];
		$params  = [];
		$this->_db->setQuery('SHOW COLUMNS FROM #__contenttemplater');
		$dbkeys = $this->_db->loadObjectList('Field');
		$dbkeys = array_keys($dbkeys);

		foreach ($data as $key => $val)
		{
			if (in_array($key, $dbkeys))
			{
				$newdata[$key] = $val;
			}
			else
			{
				$params[$key] = $val;
			}
		}

		$newdata['params'] = json_encode($params);

		return $newdata;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 */
	protected function canDelete($record)
	{
		if ($record->published != -2)
		{
			return false;
		}

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		return $user->authorise('core.admin', 'com_contenttemplater');
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_contenttemplater.edit.item.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function replaceVars(&$str)
	{
	}

	public function replaceVarsArticle(&$str)
	{
	}

	public function replaceVarsDate(&$str)
	{
	}

	public function replaceVarsRandom(&$str)
	{
	}

	public function replaceVarsTemplate(&$str)
	{
	}

	public function replaceVarsText(&$str)
	{
	}

	public function replaceVarsUser(&$str)
	{
	}

	private function getArticleById($id = 0)
	{
	}

	private function getText($id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.content')
			->from('#__contenttemplater as c');

		if (is_numeric($id))
		{
			$query->where('c.id = ' . (int) $id);
		}
		else
		{
			$query->where('c.name = ' . $db->quote($id));
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	private function incrementName(&$name, $id = 0)
	{
		while ($this->isNameAvailable($name, $id))
		{
			$name = RL_String::increment($name, $id);
		}
	}

	private function isNameAvailable($name, $id = 0)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('id')
			->from('#__contenttemplater')
			->where($db->quoteName('name') . ' = ' . $db->quote($name))
			->where($db->quoteName('published') . ' != -2')
			->setLimit(1);

		if ($id)
		{
			$query->where($db->quoteName('id') . ' != ' . (int) $id);
		}

		$db->setQuery($query);

		return (boolean) $db->loadResult();
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	private function removeSettings($data, $prefixes = ['jform_'], $keep = true)
	{
		$return = (object) [];

		foreach ($data as $key => $val)
		{
			$pass = false;

			foreach ($prefixes as $prefix)
			{
				if (strpos($key, $prefix) === 0)
				{
					$pass = true;
					break;
				}
			}

			if (($pass && ! $keep) || ( ! $pass && $keep))
			{
				continue;
			}

			$return->{$key} = $val;
		}

		return $return;
	}
}
