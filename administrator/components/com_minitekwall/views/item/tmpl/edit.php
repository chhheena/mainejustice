<?php
/**
* @title		Minitek Wall
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "item.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField('description')->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_minitekwall&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">

		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_MINITEKWALL_FIELDSET_CONTENT', true)); ?>
			<div class="row-fluid">

				<div class="span9">
					<fieldset class="adminform">

						<?php foreach ($this->form->getGroup('images') as $field) : ?>
							<?php echo $field->getControlGroup(); ?>
						<?php endforeach; ?>

						<?php echo $this->form->getGroup('urls')['jform_urls_title_url']->getControlGroup(); ?>

						<?php echo $this->form->renderField('category'); ?>
						<?php echo $this->form->getGroup('urls')['jform_urls_category_url']->getControlGroup(); ?>

						<?php echo $this->form->renderField('tags'); ?>

						<?php echo $this->form->renderField('author'); ?>
						<?php echo $this->form->getGroup('urls')['jform_urls_author_url']->getControlGroup(); ?>

						<?php echo $this->form->renderField('description'); ?>

					</fieldset>
				</div>

				<div class="span3">
					<fieldset class="form-vertical">

						<?php echo $this->form->renderField('groupid'); ?>

						<?php echo $this->form->renderField('state'); ?>

						<?php echo $this->form->renderField('access'); ?>

					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_MINITEKWALL_FIELDSET_PUBLISHING', true)); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</div>

</form>
