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

	<div class="source-thumbnails">

		<div class="thumbnail">
			<i class="fa fa-joomla mw-source-icon"></i>
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_JOOMLA'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceJoomla')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

		<div class="thumbnail">
			<i class="fa fa-pencil-square-o mw-source-icon"></i>
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_CUSTOM_ITEMS'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceCustom')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

		<div class="thumbnail">
			<img src="components/com_minitekwall/assets/images/icon-48-k2.png" alt="K2" />
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_K2'); ?></span>
			</div>
			<?php
			$k2 = JPATH_ROOT.DS.'components'.DS.'com_k2';
			if (file_exists($k2.DS.'k2.php')) { ?>
				<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceK2')">
					<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
				</button>
			<?php } else { ?>
				<button class="btn btn-default disabled" onclick="return false;">
					<?php echo JText::_('COM_MINITEKWALL_K2_NOT_INSTALLED'); ?>
				</button>
			<?php } ?>
		</div>

		<div class="thumbnail">
			<img src="components/com_minitekwall/assets/images/icon-48-virtuemart.png" alt="Virtuemart" />
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_VIRTUEMART'); ?></span>
			</div>
			<?php
			$vm = JPATH_ROOT.DS.'components'.DS.'com_virtuemart';
			if (file_exists($vm.DS.'virtuemart.php')) { ?>
				<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceVirtuemart')">
					<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
				</button>
			<?php } else { ?>
				<button class="btn btn-default disabled" onclick="return false;">
					<?php echo JText::_('COM_MINITEKWALL_VIRTUEMART_NOT_INSTALLED'); ?>
				</button>
			<?php } ?>
		</div>

		<div class="thumbnail">
			<img src="components/com_minitekwall/assets/images/icon-48-jomsocial.png" alt="Jomsocial" />
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_JOMSOCIAL'); ?></span>
			</div>
			<?php
			$js = JPATH_ROOT.DS.'components'.DS.'com_community';
			if (file_exists($js.DS.'community.php')) { ?>
				<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceJomsocial')">
					<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
				</button>
			<?php } else { ?>
				<button class="btn btn-default disabled" onclick="return false;">
					<?php echo JText::_('COM_MINITEKWALL_JOMSOCIAL_NOT_INSTALLED'); ?>
				</button>
			<?php } ?>
		</div>

		<div class="thumbnail">
			<img src="components/com_minitekwall/assets/images/icon-48-easyblog.png" alt="Easyblog" />
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_EASYBLOG'); ?></span>
			</div>
			<?php
			$easyblog = JPATH_ROOT.DS.'components'.DS.'com_easyblog';
			if (file_exists($easyblog.DS.'easyblog.php')) { ?>
				<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceEasyblog')">
					<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
				</button>
			<?php } else { ?>
				<button class="btn btn-default disabled" onclick="return false;">
					<?php echo JText::_('COM_MINITEKWALL_EASYBLOG_NOT_INSTALLED'); ?>
				</button>
			<?php } ?>
		</div>

		<div class="thumbnail">
			<img src="components/com_minitekwall/assets/images/icon-48-easysocial.png" alt="Easyblog" />
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_EASYSOCIAL'); ?></span>
			</div>
			<?php
			$easysocial = JPATH_ROOT.DS.'components'.DS.'com_easysocial';
			if (file_exists($easysocial.DS.'easysocial.php')) { ?>
				<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceEasysocial')">
					<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
				</button>
			<?php } else { ?>
				<button class="btn btn-default disabled" onclick="return false;">
					<?php echo JText::_('COM_MINITEKWALL_EASYSOCIAL_NOT_INSTALLED'); ?>
				</button>
			<?php } ?>
		</div>

		<div class="thumbnail">
			<i class="fa fa-folder-open mw-source-icon"></i>
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_IMAGE_FOLDER'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectSourceFolder')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

		<div class="thumbnail">
			<i class="fa fa-rss mw-source-icon"></i>
			<div class="thumbnail-title">
				<span><?php echo JText::_('COM_MINITEKWALL_RSS_FEED'); ?></span>
			</div>
			<button class="btn btn-info" onclick="Joomla.submitbutton('widget.selectRSSFeed')">
				<?php echo JText::_('COM_MINITEKWALL_SELECT'); ?>
			</button>
		</div>

	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
