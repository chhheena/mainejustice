<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_minitekwall&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="widget-form" class="form-validate">

	<div class="type-thumbnails">

		<div class="thumbnail">
			<img src="<?php echo JURI::root(true).'/administrator/components/com_minitekwall/assets/images/logo.png'; ?>">
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_WIDGET_TYPE_MASONRY'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectMasonry')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

		<div class="thumbnail">
			<img src="<?php echo JURI::root(true).'/administrator/components/com_minitekwall/assets/images/scroller/logo.png'; ?>">
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_WIDGET_TYPE_SCROLLER'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectScroller')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
