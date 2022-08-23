<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\Document as RL_Document;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

RL_Document::loadFormDependencies();

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

$script = "
Joomla.submitbutton = function(task)
{
	if (task == 'style.cancel' || document.formvalidator.isValid(document.getElementById('style-form'))) {
		Joomla.submitform(task, document.getElementById('style-form'));
	}
};";
if ($user->authorise('core.admin'))
{
	$script .= "
	jQuery(document).ready(function()
	{
		// add alert on remove assignment buttons
		jQuery('button.rl_remove_assignment').click(function()
		{
			if(confirm('" . str_replace('<br>', '\n', JText::_('ATP_DISABLE_ASSIGNMENT', true)) . "')) {
				window.open(jQuery('div#toolbar-options a').attr('href'));
			}
		});
	});";
}

RL_Document::scriptDeclaration($script);
?>

<form action="<?php echo JRoute::_('index.php?option=com_advancedtemplates&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm"
      id="style-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('JDETAILS')); ?>

		<div class="row-fluid">
			<div class="span9">
				<h3>
					<?php echo JText::_($this->item->template); ?>
				</h3>

				<div class="info-labels">
					<span class="label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_FIELD_CLIENT_LABEL'); ?>">
						<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>
					</span>
				</div>
				<div>
					<p><?php echo JText::_($this->item->xml->description); ?></p>
					<?php
					$this->fieldset = 'description';
					$description    = JLayoutHelper::render('joomla.edit.fieldset', $this);
					?>
					<?php if ($description) : ?>
						<p class="readmore">
							<a href="#" onclick="jQuery('.nav-tabs a[href=#description]').tab('show');">
								<?php echo JText::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>
				<?php
				$this->fieldset = 'basic';
				$html           = JLayoutHelper::render('joomla.edit.fieldset', $this);
				echo $html ? '<hr>' . $html : '';
				?>
			</div>
			<div class="span3">
				<?php
				// Set main fields.
				$this->fields = [
					'home',
					'client_id',
					'template',
				];
				?>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				<?php if ($this->config->show_color) : ?>
					<fieldset class="form-vertical">
						<div class="control-group">
							<div class="control-label">
								<label id="advancedparams_color-lbl" for="advancedparams_color" class="hasTooltip" title=""
								       data-original-title="<strong><?php echo JText::_('RL_COLOR', true); ?></strong><br><?php echo JText::_('ATP_COLOR_DESC', true); ?>">
									<?php echo JText::_('RL_COLOR'); ?>
								</label>
							</div>
							<div class="controls">
								<?php
								include_once(JPATH_LIBRARIES . '/joomla/form/fields/color.php');
								$colorfield = new JFormFieldColor;

								$color = (isset($this->item->advancedparams['color']) && $this->item->advancedparams['color'])
									? str_replace('##', '#', $this->item->advancedparams['color'])
									: 'none';

								$element        = new SimpleXMLElement(
									'<field
											name="advancedparams[color]"
											id="advancedparams_color"
											type="color"
											control="simple"
											default=""
											colors="' . ($this->config->main_colors ?? '') . '"
											split="4"
											/>'
								);
								$element->value = $color;

								$colorfield->setup($element, $color);

								echo $colorfield->__get('input');
								?>
							</div>
						</div>
					</fieldset>
				<?php endif; ?>
				<fieldset class="form-vertical">
					<?php echo $this->render($this->assignments, 'note'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if ($description) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
			<?php echo $description; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->fieldsets        = [];
		$this->ignore_fieldsets = ['basic', 'description'];
		echo JLayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($user->authorise('core.edit', 'com_menu') && $this->item->client_id == 0 && $this->canDo->get('core.edit.state')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'assignment', JText::_('ATP_ASSIGNMENTS')); ?>
			<?php echo $this->loadTemplate('assignment'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->config->show_notes) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'notes', JText::_('ATP_NOTES')); ?>
			<div class="form-vertical">
				<?php echo $this->render($this->assignments, 'notes'); ?>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<?php echo JHtml::_('form.token'); ?>

		<?php if ($this->config->show_switch) : ?>
			<div style="text-align:right">
				<a href="<?php echo JRoute::_('index.php?option=com_templates&force=1&view=style&id=' . (int) $this->item->id); ?>"><?php echo JText::_('ATP_SWITCH_TO_CORE'); ?></a>
			</div>
		<?php endif; ?>
	</div>
</form>
