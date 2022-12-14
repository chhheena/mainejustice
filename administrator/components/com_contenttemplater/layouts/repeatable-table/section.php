<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm  $form      The form instance for render the section
 * @var string $basegroup The base group name
 * @var string $group     Current group name
 * @var array  $buttons   Array of the buttons that will be rendered
 */
extract($displayData);

$fields = $form->getGroup('');

?>

<tr
		class="subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?>"
		data-base-name="<?php echo $basegroup; ?>"
		data-group="<?php echo $group; ?>"
>
	<td>
		<a class="sortable-handler group-move group-move-<?php echo $unique_subform_id; ?>" style="cursor: move;" aria-label="<?php echo JText::_('JGLOBAL_FIELD_MOVE'); ?>">
			<span class="icon-menu" aria-hidden="true"></span>
		</a>
	</td>
	<td data-column="<?php echo strip_tags($fields['jform_fields__' . $group . '__field']->label); ?>">
		<?php echo $fields['jform_fields__' . $group . '__field']->renderField(['hiddenLabel' => true]); ?>
		<?php echo $fields['jform_fields__' . $group . '__field_name']->renderField(['hiddenLabel' => true]); ?>
	</td>
	<td data-column="<?php echo strip_tags($fields['jform_fields__' . $group . '__field_value']->label); ?>">
		<?php echo $fields['jform_fields__' . $group . '__field_value']->renderField(['hiddenLabel' => true]); ?>
	</td>

	<td>
		<div class="btn-group">
			<a class="btn btn-mini button btn-danger group-remove group-remove-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_REMOVE'); ?>">
				<span class="icon-minus" aria-hidden="true"></span>
			</a>
		</div>
	</td>
</tr>
