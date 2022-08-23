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

class JFormFieldTypeId extends JFormFieldList
{
	public $type = 'TypeId';

	public function getOptions()
	{
		$options = Array(
			Array(
				'value' => '',
				'text' => JText::_('COM_MINITEKWALL_SELECT_TYPE_ID')
			),
			Array(
				'value' => 'masonry',
				'text' => JText::_('COM_MINITEKWALL_OPTION_MASONRY')
			),
			Array(
				'value' => 'scroller',
				'text' => JText::_('COM_MINITEKWALL_OPTION_SCROLLER')
			)
		);

		return $options;
	}
}
