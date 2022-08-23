<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldGroupId extends JFormFieldList
{
	public $type = 'GroupId';

	protected function getOptions()
	{
		$db = JFactory::getDBO();

		$query = 'SELECT g.id as value, g.name as text FROM #__minitek_source_groups g ';
		$query .= 'WHERE state = 1 ORDER BY g.name';

		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$options = array();

		foreach ($groups as $group)
		{
			$options[] = JHTML::_('select.option', $group->value, $group->text);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
