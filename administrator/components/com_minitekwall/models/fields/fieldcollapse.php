<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class JFormFieldFieldCollapse extends JFormField
{
	public $type = 'FieldCollapse';
	private $params = null;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$label = $this->get('label');
		$description = $this->get('description');
		$class = $this->get('class');

		if ($description)
		{
			$label = JText::_($label);
			$description = JText::_($description);
		}

		if ($description)
		{
			$html = '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
				$html .= '<div class="panel panel-default">';
					$html .= '<div class="panel-heading" role="tab" id="headingOne">';
						$html .= '<h4 class="panel-title">';
							$html .= '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">';
								$html .= $label;
								$html .= '&nbsp;&nbsp;<i class="fa fa-caret-down"></i>';
							$html .= '</a>';
						$html .= '</h4>';
					$html .= '</div>';
					$html .= '<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
						$html .= '<div class="panel-body">';
							$html .= $description;
						$html .= '</div>';
					$html .= '</div>';
				 $html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	private function get($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
