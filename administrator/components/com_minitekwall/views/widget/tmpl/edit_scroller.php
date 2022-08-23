<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_layout', JText::_('COM_MINITEKWALL_FIELD_WIDGET_LAYOUT_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_layout') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_image_settings', JText::_('COM_MINITEKWALL_FIELD_WIDGET_IMAGES_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_image_settings') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_detailbox', JText::_('COM_MINITEKWALL_FIELD_WIDGET_DETAIL_BOX_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_detailbox') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_hoverbox', JText::_('COM_MINITEKWALL_FIELD_WIDGET_HOVER_BOX_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_hoverbox') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_pagination', JText::_('COM_MINITEKWALL_FIELD_WIDGET_NAVIGATION_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_navigation') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'scroller_effects', JText::_('COM_MINITEKWALL_FIELD_WIDGET_EFFECTS_PARAMS', true)); ?>

	<div class="row-fluid">
		<div class="span12">
			<?php foreach ($this->scrollerform->getFieldset('scroller_effects') as $field): ?>
			<div class="control-group form-inline">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>

<?php echo JHtml::_('bootstrap.endTab');
