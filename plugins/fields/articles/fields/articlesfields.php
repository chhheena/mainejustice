<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use RegularLabs\Library\Field as RL_Field;
use RegularLabs\Library\Form as RL_Form;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class JFormFieldArticlesFields extends RL_Field
{
	public $type = 'ArticlesFields';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		if ( ! is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$options = $this->getOptions();

		return RL_Form::selectListSimple(
			$options,
			$this->name,
			$this->value,
			$this->id,
			0,
			true,
			! FieldsHelper::canEditFieldValue($this)
		);
	}

	protected function getOptions()
	{
		$options = parent::getOptions();

		$fields = FieldsHelper::getFields('com_content.article');

		foreach ($fields as $field)
		{
			if ($field->type != 'articles')
			{
				continue;
			}

			$options[] = JHtml::_('select.option', $field->id, $field->title);
		}

		// Sorting the fields based on the text which is displayed
		usort(
			$options,
			function ($a, $b) {
				return strcmp($a->text, $b->text);
			}
		);

		return $options;
	}
}
