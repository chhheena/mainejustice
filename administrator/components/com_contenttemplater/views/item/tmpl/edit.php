<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Language as RL_Language;

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

RL_Document::style('regularlabs/style.min.css');
RL_Document::style('contenttemplater/style.min.css', '10.2.0');

RL_Language::load('com_content', JPATH_ADMINISTRATOR);

RL_Document::loadFormDependencies();
?>
<style>
    #toolbar-popup-help {
        float: right;
    }
</style>

<form action="<?php echo JRoute::_('index.php?option=com_contenttemplater'); ?>" method="post"
      name="adminForm" id="item-form" class="form-validate">

	<div class="form-inline form-inline-header">
		<?php echo $this->item->form->renderField('name'); ?>
	</div>

	<div class="row-fluid form-horizontal">
		<div class="span9 span-md-8">

			<?php echo JHtml::_('bootstrap.startTabSet', 'main', ['active' => 'template']); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'main', 'template', JText::_('CT_TEMPLATE')); ?>
			<?php echo $this->render($this->item->form, 'template'); ?>

			<div class="form-inline form-inline-header">
				<div class="form-vertical">
					<?php echo $this->render($this->item->form, 'template-name'); ?>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.startTabSet', 'template', ['active' => 'content-content']); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'content-content', JText::_('RL_CONTENT')); ?>
			<?php echo $this->render($this->item->form, 'template-content'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'general', JText::_('COM_CONTENT_ARTICLE_DETAILS')); ?>
			<div class="row-fluid">
				<fieldset><?php echo $this->render($this->item->form, 'template-details'); ?></fieldset>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'images', JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES')); ?>
			<div class="row-fluid">
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<?php echo $this->render($this->item->form, 'template-images'); ?>
					</fieldset>
				</div>
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<?php echo $this->render($this->item->form, 'template-urls'); ?>
					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'basic', JText::_('COM_CONTENT_ATTRIBS_FIELDSET_LABEL')); ?>
			<fieldset>
				<?php echo $this->render($this->item->form, 'template-basic'); ?>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'fields', JText::_('JGLOBAL_FIELDS')); ?>
			<fieldset>
				<?php echo $this->render($this->item->form, 'template-fields'); ?>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'contentpublishing', JText::_('COM_CONTENT_FIELDSET_PUBLISHING')); ?>
			<div class="row-fluid">
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<?php echo $this->render($this->item->form, 'template-publishing-left'); ?>
					</fieldset>
				</div>
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<?php echo $this->render($this->item->form, 'template-publishing-right'); ?>
					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'template', 'editorconfig', JText::_('COM_CONTENT_SLIDER_EDITOR_CONFIG')); ?>
			<fieldset>
				<?php echo $this->render($this->item->form, 'template-editorconfig'); ?>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'main', 'editorbutton', JText::_('CT_EDITOR_BUTTON')); ?>
			<fieldset><?php echo $this->render($this->item->form, 'editorbutton'); ?></fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'main', 'autoload', JText::_('CT_AUTO_LOAD')); ?>
			<div class="row-fluid">
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<legend><?php echo JText::_('CT_LOAD_BY_DEFAULT'); ?></legend>
						<?php echo $this->render($this->item->form, 'autoload-default'); ?>
					</fieldset>
				</div>
				<div class="span6 span-md-12 span-lg-12">
					<fieldset>
						<legend><?php echo JText::_('CT_LOAD_BY_URL'); ?></legend>
						<?php echo $this->render($this->item->form, 'autoload-url'); ?>
					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'main', 'assignments', JText::_('RL_PUBLISHING_ASSIGNMENTS')); ?>
			<fieldset>
				<?php echo $this->render($this->item->form, 'assignments'); ?>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>

		<div class="span3 span-md-4 form-vertical">
			<fieldset>
				<?php echo $this->render($this->item->form, 'details'); ?>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task) {
		var f = document.getElementById('item-form');
		if (task == 'item.cancel') {
			Joomla.submitform(task, f);
			return;
		}

		// do field validation
		if (f['jform[name]'].value.trim() == "") {
			alert("<?php echo JText::_('CT_THE_ITEM_MUST_HAVE_A_NAME', true); ?>");
			return;
		}

		Joomla.submitform(task, f);
	};
</script>
