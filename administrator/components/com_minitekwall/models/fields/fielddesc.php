<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class JFormFieldFieldDesc extends JFormField
{
	public $type = 'FieldDesc';
	private $params = null;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$description = $this->get('description');
		$class = $this->get('class');

		if ($description)
		{
			$description = JText::_($description);
		}

		if ($description)
		{
			$html = '<div class="alert alert-'.$class.'"><p></p>'.$description.'</div>';
		}

		return $html;
	}

	private function get($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
