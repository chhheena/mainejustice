<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
$k2 = JPATH_ROOT.DS.'components'.DS.'com_k2';
if (file_exists($k2.DS.'k2.php'))
{
	jimport('joomla.form.formfield');

	class JFormFieldK2tags extends JFormField
	{
		var	$type = 'K2tags';

		function getInput()
		{
			$db = JFactory::getDBO();
			$query = " SELECT * FROM #__k2_tags_xref tags_xref  LEFT JOIN #__k2_tags tags ON tags.id = tags_xref.tagID WHERE published=1 GROUP BY tags.id ORDER BY name" ;
			$db->setQuery( $query );

			$data = $db->loadObjectList();
			$items = array();

			foreach ( $data as $item )
			{
				$items[] = JHTML::_('select.option',  $item->id, '   '.$item->name );
			}

			$output= JHTML::_('select.genericlist',  $items, $this->name."[]", 'class="inputbox" style="min-width:10%" multiple="multiple" size="10" ', 'value', 'text', $this->value, $this->id );

			return $output;
		}
	}
}
