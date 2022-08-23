<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class JFormFieldPhotosAlbums extends JFormField
{
  function getInput()
  {
    $document = JFactory::getDocument();
    JHtml::_('behavior.framework');

    $db = JFactory::getDBO();
    $query = 'SELECT p.* FROM #__community_photos_albums p ORDER BY name, id';
    $db->setQuery($query);
    $list = $db->loadObjectList();

    $pitems = array();

    foreach ($list as $item)
    {
        $pitems[] = JHTML::_('select.option', $item->id, $item->name);
    }

    $output = JHTML::_('select.genericlist', $pitems, $this->name, 'class="inputbox" multiple="true" size="10"', 'value', 'text', $this->value, $this->id);

    return $output;
  }
}
