<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$isNew = ($this->item->id == 0);
$moduleIsInstalled = $this->checkModuleIsInstalled;
?>

<div class="modal fade" id="createModule" tabindex="-1" role="dialog" aria-labelledby="createModuleLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo JText::_('COM_MINITEKWALL_MODAL_CLOSE'); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<h3 class="modal-title" id="createModuleLabel">
					<?php echo JText::_('COM_MINITEKWALL_MODAL_PUBLISH_WIDGET_IN_MODULE'); ?>
				</h3>
			</div>

			<div class="modal-body">

				<div class="row-fluid">

					<?php if (!$moduleIsInstalled) { ?>

					<div class="span12 text-center">
						<h3><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULE_NOT_FOUND'); ?></h3>
						<p><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULE_NOT_FOUND_DESC'); ?></p>
						<a class="btn btn-primary" href="https://www.minitek.gr/downloads/minitek-wall-module" target="_blank">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD'); ?>
						</a>
					</div>

					<?php } else if ($moduleIsInstalled && !$isNew) { ?>

						<div class="modal-thumbnails">
							<div class="thumbnail">
								<h3><?php echo JText::_('COM_MINITEKWALL_MODAL_IN_MODULE_POSITION'); ?></h3>
								<p><?php echo JText::_('COM_MINITEKWALL_MODAL_IN_MODULE_POSITION_DESC'); ?></p>

								<button class="btn btn-success" data-toggle="modal" data-target="#createModule" onclick="Joomla.submitbutton('widget.createModule')">
									<?php echo JText::_('COM_MINITEKWALL_MODAL_CREATE_MODULE'); ?>
								</button>
							</div>

							<div class="thumbnail">
								<h3><?php echo JText::_('COM_MINITEKWALL_MODAL_LOAD_POSITION_PLUGIN'); ?></h3>
								<p><?php echo JText::_('COM_MINITEKWALL_MODAL_LOAD_POSITION_PLUGIN_DESC'); ?></p>

								<button class="btn btn-success" data-toggle="modal" data-target="#createModule" onclick="Joomla.submitbutton('widget.createModuleforPlugin')">
									<?php echo JText::_('COM_MINITEKWALL_MODAL_CREATE_MODULE'); ?>
								</button>

								<div class="alert alert-warning" role="alert">
									<p><small><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULE_SYNTAX'); ?></small></p>
									<p>&#123;loadposition minitekwall-<?php echo $this->item->id; ?>&#125;</p>
								</div>
							</div>

							<div class="thumbnail">
								<h3><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULES_ANYWHERE_PLUGIN'); ?></h3>
								<p><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULES_ANYWHERE_PLUGIN_DESC'); ?></p>

								<button class="btn btn-success" data-toggle="modal" data-target="#createModule" onclick="Joomla.submitbutton('widget.createModuleforPlugin')">
									<?php echo JText::_('COM_MINITEKWALL_MODAL_CREATE_MODULE'); ?>
								</button>

								<div class="alert alert-warning" role="alert">
									<p><small><?php echo JText::_('COM_MINITEKWALL_MODAL_MODULE_SYNTAX'); ?></small></p>
									<p>&#123;modulepos minitekwall-<?php echo $this->item->id; ?>&#125;</p>
								</div>
							</div>
						</div>

					<?php } else if ($moduleIsInstalled && $isNew) { ?>

						<div class="span12 text-center">
							<h3><?php echo JText::_('COM_MINITEKWALL_MODAL_WIDGET_NOT_SAVED'); ?></h3>
							<p><?php echo JText::_('COM_MINITEKWALL_MODAL_WIDGET_NOT_SAVED_DESC'); ?></p>
						</div>

					<?php } ?>

				</div>
			</div>
		</div>
	</div>
</div>
