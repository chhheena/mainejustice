<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.framework');

$canCreate = JFactory::getUser()->authorise('core.create', 'com_minitekwall');
$isNew = ($this->item->id == 0);
?>

<div class="widget-content">

	<div class="widget-pagination clearfix">
		<?php echo $this->loadTemplate('pagination'); ?>
	</div>

	<?php // Select Type
	if (!$this->type_id || $this->app->input->get('page') == 'type') { ?>
		<?php echo $this->loadTemplate('type'); ?>
		<script type="text/javascript">
			Joomla.submitbutton = function(task)
			{
				if (task == 'widget.cancel' || document.formvalidator.isValid(document.id('widget-form')))
				{
					Joomla.submitform(task, document.getElementById('widget-form'));
				}
			}
		</script>
		<?php
		return;
	}

	// Select Source
	if (!$this->source_id || $this->app->input->get('page') == 'source') { ?>
		<?php echo $this->loadTemplate('source'); ?>
		<script type="text/javascript">
			Joomla.submitbutton = function(task)
			{
				if (task == 'widget.cancel' || document.formvalidator.isValid(document.id('widget-form')))
				{
					Joomla.submitform(task, document.getElementById('widget-form'));
				}
			}
		</script>
		<?php
		return;
	}

	// Module (modal)
	if ($canCreate && !$isNew)
	{
		echo $this->loadTemplate('module');
	}
	?>

	<form action="<?php echo JRoute::_('index.php?option=com_minitekwall&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="widget-form" class="form-validate">

		<div class="row-fluid">

			<div class="span12 form-horizontal">

				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic')); ?>

					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'basic', JText::_('COM_MINITEKWALL_FIELD_WIDGET_GENERAL_PARAMS', true)); ?>

						<div class="row-fluid">
							<div class="span12">
								<?php foreach ($this->form->getFieldset('basic') as $field): ?>
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

					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'datasource', JText::_('COM_MINITEKWALL_FIELD_WIDGET_DATA_SOURCE_PARAMS', true)); ?>

						<?php if ($this->source_id == 'joomla') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('joomla_source'); ?>
									<?php foreach ($this->form->getGroup('joomla_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'custom') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('custom_source'); ?>
									<?php foreach ($this->form->getGroup('custom_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'k2') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('k2_source'); ?>
									<?php foreach ($this->form->getGroup('k2_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'virtuemart') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('virtuemart_source'); ?>
									<?php foreach ($this->form->getGroup('virtuemart_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'jomsocial') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('jomsocial_source'); ?>
									<?php foreach ($this->form->getGroup('jomsocial_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'easyblog') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('easyblog_source'); ?>
									<?php foreach ($this->form->getGroup('easyblog_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'easysocial') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('easysocial_source'); ?>
									<?php foreach ($this->form->getGroup('easysocial_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'folder') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('folder_source'); ?>
									<?php foreach ($this->form->getGroup('folder_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } else if ($this->source_id == 'rss') { ?>

							<div class="row-fluid">
								<div class="span12">
									<?php echo $this->form->getControlGroup('rss_source'); ?>
									<?php foreach ($this->form->getGroup('rss_source') as $field) : ?>
										<?php echo $field->getControlGroup(); ?>
									<?php endforeach; ?>
								</div>
							</div>

						<?php } ?>

					<?php echo JHtml::_('bootstrap.endTab'); ?>

					<?php if ($this->type_id == 'masonry') { ?>

						<?php echo $this->loadTemplate('masonry'); ?>

					<?php } else if ($this->type_id == 'scroller') { ?>

						<?php echo $this->loadTemplate('scroller'); ?>

					<?php } ?>

				</div>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" id="jform_type_id" name="jform[type_id]" value="<?php echo $this->type_id; ?>" />
			<input type="hidden" id="jform_source_id" name="jform[source_id]" value="<?php echo $this->source_id; ?>" />

			<?php echo JHtml::_('form.token'); ?>

		</div>

	</form>

</div>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'widget.cancel' || document.formvalidator.isValid(document.id('widget-form')))
		{
			Joomla.submitform(task, document.getElementById('widget-form'));
		}
	}
</script>
