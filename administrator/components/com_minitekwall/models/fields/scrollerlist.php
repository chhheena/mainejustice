<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('radio');

class JFormFieldScrollerList extends JFormFieldRadio
{
	public $type = 'ScrollerList';

	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.
		$class     = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$readonly  = $this->readonly;

		// Start the radio field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . $required . $autofocus . $disabled . ' >';

		// Get the field options.
		$options = $this->getOptions();

		// Build the radio field output.
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';

			$disabled = !empty($option->disable) || ($readonly && !$checked);

			$disabled = $disabled ? ' disabled' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			$html[] = '<div class="scroller-radio '.(string) $option->value.'">';

				$html[] = '<label for="' . $this->id . $i . '"' . $class . ' >';

					$html[] = '<p>';
						$html[] = JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname));
					$html[] = '</p>';

					$html[] = '<div class="scroller-radio-demo-cont">';

						$html[] = '<div class="scroller-radio-demo">';

							$html[] = '<img alt="" src="components/com_minitekwall/assets/images/scroller/'.$option->image.'">';

						$html[] = '</div>';

					$html[] = '</div>';

				$html[] = '</label>';

				$html[] = '<div class="scroller-radio-actions">';

					$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
					. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . $onclick
					. $onchange . $disabled . ' />';

				$html[] = '</div>';

			$html[] = '</div>';

			$required = '';
		}

		// End the radio field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	protected function getOptions()
	{
		$elements = Array(
			Array(
				'value' => 'image_scroller',
				'image' => 'scroller_i.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_IMAGE_SCROLLER'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'article_scroller_1',
				'image' => 'scroller_a1.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_ARTICLE_SCROLLER_1'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'article_scroller_2',
				'image' => 'scroller_a2.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_ARTICLE_SCROLLER_2'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'article_scroller_3',
				'image' => 'scroller_a3.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_ARTICLE_SCROLLER_3'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'article_scroller_4',
				'image' => 'scroller_a4.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_ARTICLE_SCROLLER_4'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'article_scroller_5',
				'image' => 'scroller_a5.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_ARTICLE_SCROLLER_5'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'media_scroller',
				'image' => 'scroller_m.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_MEDIA_SCROLLER'),
				'class' => 'scroller-radio-input'
			),
			Array(
				'value' => 'reveal_scroller',
				'image' => 'scroller_r.jpg',
				'text' => JText::_('COM_MINITEKWALL_FIELD_REVEAL_SCROLLER'),
				'class' => 'scroller-radio-input'
			)
		);

		foreach ($elements as $option)
		{
			$disabled = false;

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option['text']), 'value', 'text',
				$disabled
			);

			// Set some option attributes.
			$tmp->class = $option['class'];
			$tmp->image = $option['image'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
