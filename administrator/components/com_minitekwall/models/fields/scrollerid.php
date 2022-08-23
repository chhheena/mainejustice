<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class JFormFieldScrollerId extends JFormField
{
  function getInput()
  {
    JHtml::_('behavior.framework');

    $db = JFactory::getDBO();
    $query = ' SELECT * '
		. ' FROM '. $db->quoteName('#__minitek_wall_widgets') . ' '
		. ' WHERE '.$db->quoteName('state').' = '.$db->Quote('1').' '
		. ' AND '.$db->quoteName('type_id').' = '.$db->Quote('scroller').' '
		. ' ORDER BY name';
    $db->setQuery($query);
    $items = $db->loadObjectList();

    if ($items)
    {
  		$list = array();
      foreach ($items as $v)
      {
        $pt = $v->name;
        array_push($list, $v);
      }

  		$items = array();

  		foreach ($list as $item)
  		{
  			$item->name = JString::str_ireplace('&#160;', '- ', $item->name);
  			$items[] = JHTML::_('select.option', $item->id, $item->name);
  		}

  		$output = JHTML::_('select.genericlist', $items, $this->name, 'class="inputbox" size="10"', 'value', 'text', $this->value, $this->id);
  	}
    else
    {
  		$output = JText::_('COM_MINITEKWALL_NO_SCROLLER_WIDGETS_FOUND');
  	}

	   return $output;
  }
}
