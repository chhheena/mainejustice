<?php
/**
* @title		Minitek Wall
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallTableGrid extends JTable
{
	public function __construct(&$_db)
	{
		$this->checked_out_time = $_db->getNullDate();
		parent::__construct('#__minitek_wall_grids', 'id', $_db);
		$this->setColumnAlias('published', 'state');
	}

	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_minitekwall.grid.' . (int) $this->$k;
	}

	protected function _getAssetTitle()
	{
		return $this->name;
	}

	public function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}

	public function check()
	{
		if (trim($this->name) == '')
		{
			$this->setError(JText::_('COM_MINITEKWALL_GRIDS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
}
