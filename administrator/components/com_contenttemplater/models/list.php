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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\ListModel as JModelList;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

jimport('joomla.application.component.modellist');

/**
 * List Model
 */
class ContentTemplaterModelList extends JModelList
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
	public function __construct($config = [])
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'ordering', 'a.ordering',
				'state', 'a.published',
				'name', 'a.name',
				'description', 'a.description',
				'category', 'a.category',
				'id', 'a.id',
			];
		}

		parent::__construct($config);
	}

	/**
	 * Copy Method
	 * Copy all items specified by array cid
	 * and set Redirection to the list of items
	 */
	public function copy($ids, $model)
	{
		foreach ($ids as $id)
		{
			$model->copy($id);
		}

		$msg = JText::sprintf('Items copied', count($ids));
		JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list', $msg);
	}

	/**
	 * Export Method
	 * Export the selected items specified by id
	 */
	public function export($ids)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.name')
			->select('c.description')
			->select('c.category')
			->select('c.content')
			->select('c.params')
			->select('c.published')
			->select('c.ordering')
			->from('#__contenttemplater as c')
			->where('c.id IN ( ' . implode(', ', $ids) . ' )');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$this->exportDataToFile($rows);
	}

	public function exportDataToFile($rows)
	{
		$filename = 'ContentTemplater Items';

		if (count($rows) == 1)
		{
			$name = RL_String::strtolower(RL_String::html_entity_decoder($rows[0]->name));
			$name = RL_RegEx::replace('[^a-z0-9_-]', '_', $name);
			$name = trim(RL_RegEx::replace('__+', '_', $name), '_-');

			$filename = 'ContentTemplater Item (' . $name . ')';
		}

		$string = json_encode($rows);
		$this->exportStringToFile($string, $filename);
	}

	public function exportStringToFile($string, $filename)
	{
		// SET DOCUMENT HEADER
		if (RL_RegEx::match('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
		{
			$UserBrowser = "Opera";
		}
		elseif (RL_RegEx::match('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
		{
			$UserBrowser = "IE";
		}
		else
		{
			$UserBrowser = '';
		}

		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();

		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		if ($UserBrowser == 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . '.json"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Content-Disposition: attachment; filename="' . $filename . '.json"');
			header('Pragma: no-cache');
		}

		// PRINT STRING
		echo $string;
		die;
	}

	public function getHasCategories()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__contenttemplater'))
			->where($db->quoteName('category') . ' != ' . $db->quote(''));

		$db->setQuery($query);

		return $db->loadResult();
	}

	public function getItems($getall = false, $ordering = '')
	{
		// Get a storage key.
		$store = $this->getStoreId('', $getall, $ordering);

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		if ($getall)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				// Select the required fields from the table.
				->select('a.*')
				->from($db->quoteName('#__contenttemplater', 'a'))
				->where($this->_db->quoteName('a.published') . ' = 1');
		}
		else
		{
			$query = $this->_getListQuery();
		}

		if ($ordering)
		{
			$query->clear('order');

			if ($ordering == 'a.ordering')
			{
				$query->order($db->quoteName('a.category'));
			}

			$query->clear('order')->order($ordering);
		}

		if ($getall)
		{
			$this->_db->setQuery($query);
			$items = $this->_db->loadObjectList();
		}
		else
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		foreach ($items as $i => $item)
		{
			$isini  = ((substr($item->params, 0, 1) != '{') && (substr($item->params, -1, 1) != '}'));
			$params = RL_Parameters::getObjectFromRegistry($item->params, JPATH_ADMINISTRATOR . '/components/com_contenttemplater/item_params.xml');
			foreach ($params as $key => $val)
			{
				if ( ! isset($item->{$key}) && ! is_object($val))
				{
					$items[$i]->{$key} = $val;
				}
			}
			unset($items[$i]->params);

			if ($isini)
			{
				foreach ($items[$i] as $key => $val)
				{
					if (is_string($val) && $key != 'content')
					{
						$items[$i]->{$key} = stripslashes($val);
					}
				}
			}
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			// Select the required fields from the table.
			->select(
				$this->getState(
					'list.select',
					'a.*'
				)
			)
			->from($db->quoteName('#__contenttemplater', 'a'));

		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.published') . ' = ' . ( int ) $state);
		}
		else if ($state == '')
		{
			$query->where('( ' . $db->quoteName('a.published') . ' IN ( 0,1,2 ) )');
		}

		$category = $this->getState('filter.category');
		if ($category != '')
		{
			$query->where($db->quoteName('a.category') . ' = ' . $db->quote($category));
		}

		$button_enable_in_frontend = $this->getState('filter.button_enable_in_frontend');
		if ($button_enable_in_frontend != '')
		{
			$query->where($db->quoteName('a.params') . ' LIKE ' . $db->quote('%"button_enable_in_frontend":"' . $button_enable_in_frontend . '"%'));
		}

		$load_enabled = $this->getState('filter.load_enabled');
		if ($load_enabled != '')
		{
			$query->where($db->quoteName('a.params') . ' LIKE ' . $db->quote('%"load_enabled":"' . $load_enabled . '"%'));
		}

		// Filter the list over the search string if set.
		$search = $this->getState('filter.search');
		if ( ! empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . ( int ) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where(
					'( ' . $db->quoteName('a.name') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('a.description') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('a.category') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('a.content') . ' LIKE ' . $search . ' )'
				);
			}
		}

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'a.ordering');
		$direction = $this->state->get('list.direction', 'ASC');

		if ($ordering == 'a.ordering')
		{
			$query->order($db->quoteName('a.category') . ' ' . $db->escape($direction));
		}

		$query->order($db->quoteName($db->escape($ordering)) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param string    A prefix for the store id.
	 *
	 * @return    string    A store id.
	 */
	protected function getStoreId($id = '', $getall = 0, $ordering = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search')
			. ':' . $this->getState('filter.state')
			. ':' . $getall
			. ':' . $ordering;

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	public function getItemsFromBak($data)
	{
		if ($data[0] == '<')
		{
			return $this->getItemsFromBakLegacy($data);
		}

		return $this->getItemsFromJson($data);
	}

	public function getItemsFromBakLegacy($data)
	{
		// Old format
		$data = explode('<CT_ITEM_START>', $data);

		$items = [];
		foreach ($data as $data_item)
		{
			$data_item = trim(str_replace('<CT_ITEM_END>', '', $data_item));
			if ( ! $data_item)
			{
				continue;
			}

			$data_item_keyvals = explode('<CT_KEY>', $data_item);
			$item              = [];
			foreach ($data_item_keyvals as $data_item_keyval)
			{
				$data_item_keyval = trim(str_replace('<CT_END>', '', $data_item_keyval));
				if ($data_item_keyval)
				{
					$data_item_keyval           = explode('<CT_VAL>', $data_item_keyval);
					$item[$data_item_keyval[0]] = (isset($data_item_keyval[1])) ? $data_item_keyval[1] : '';
				}
			}

			$items[] = $item;
		}

		return $items;
	}

	public function getItemsFromImportData($data, $file_format)
	{
		switch ($file_format)
		{
			case 'ctbak':
				return $this->getItemsFromBak($data);

			default:
				return $this->getItemsFromJson($data);
		}
	}

	public function getItemsFromJson($data)
	{
		$items = json_decode($data, true);

		if (is_null($items))
		{
			return [];
		}

		return $items;
	}

	/**
	 * Import Method
	 * Import the selected items specified by id
	 * and set Redirection to the list of items
	 */
	public function import($model)
	{
		$msg = JText::_('CT_PLEASE_CHOOSE_A_VALID_FILE');

		$file = JRequest::getVar('file', '', 'files', 'array');

		if ( ! is_array($file) || ! isset($file['name']))
		{
			JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list&layout=import', $msg);
		}

		$file_format = pathinfo($file['name'], PATHINFO_EXTENSION);

		if ( ! in_array($file_format, ['json', 'ctbak']))
		{
			JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list&layout=import', $msg);
		}

		jimport('joomla.filesystem.file');
		$publish_all = JFactory::getApplication()->input->getInt('publish_all', 0);

		$data = file_get_contents($file['tmp_name']);

		if (empty($data))
		{
			JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list', JText::_('File is empty!'));

			return;
		}

		$items = $this->getItemsFromImportData($data, $file_format);

		if (empty($items))
		{
			JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list&layout=import', $msg);
		}

		$msg = JText::_('Items saved');

		foreach ($items as $item)
		{
			$item['id'] = 0;

			if ($publish_all == 0)
			{
				unset($item['published']);
			}
			else if ($publish_all == 1)
			{
				$item['published'] = 1;
			}

			$saved = $model->save($item);

			if ($saved != 1)
			{
				$msg = JText::_('Error Saving Item') . ' ( ' . $saved . ' )';
			}
		}

		JFactory::getApplication()->redirect('index.php?option=com_contenttemplater&view=list', $msg);
	}
}
