<?php
/**
* @title		Minitek Wall
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallTableGroup extends JTable
{
	public function __construct(&$_db)
	{
		$this->checked_out_time = $_db->getNullDate();
		parent::__construct('#__minitek_source_groups', 'id', $_db);
		$this->setColumnAlias('published', 'state');
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_minitekwall.group.' . (int) $this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->name;
	}

	public function check()
	{
		// Check for valid name
		if (trim($this->name) == '')
		{
			$this->setError(JText::_('COM_MINITEKWALL_GROUPS_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}

		// Clean up description -- eliminate quotes and <> brackets
		if (!empty($this->description))
		{
			// Only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->description = JString::str_ireplace($bad_characters, "", $this->description);
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		// Verify that the name is unique
		$table = JTable::getInstance('Group', 'MinitekWallTable');

		if ($table->load(array('name' => $this->name)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->name = $this->name.' - '.date('D, d M Y H:i:s');
		}

		return parent::store($updateNulls);
	}
}
