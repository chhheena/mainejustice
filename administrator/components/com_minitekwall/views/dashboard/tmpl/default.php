<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$local_version = $this->utilities->localVersion();
$moduleIsInstalled = $this->utilities->checkModuleIsInstalled();
?>

<div class="minitek-dashboard">
	<?php if (!$moduleIsInstalled) { ?>
		<div class="alert alert-danger">
			<div class="update-info">
				<div>
					<span><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_MODULE_NOT_INSTALLED'); ?></span>
				</div>
				<div>
					<a class="button-success btn btn-sm btn-success" href="https://www.minitek.gr/downloads/minitek-wall-module" target="_blank">
						<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD'); ?>
					</a>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="row-fluid">

		<div class="span8">
			<div class="media">
				<div class="pull-left">
					<img class="media-object" src="<?php echo JURI::root(true).'/administrator/components/com_minitekwall/assets/images/logo.png'; ?>">
				</div>
				<div class="media-body">
			    <h2 class="media-heading"><?php echo JText::_('COM_MINITEKWALL'); ?></h2>
			    <?php echo JText::_('COM_MINITEKWALL_DESC'); ?>
			  </div>
			</div>

			<div class="dashboard-thumbnails">
				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&task=widget.add'); ?>">
						<i class="icon icon-new" style="color: #74b974;"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_NEW_WIDGET'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&view=widgets'); ?>">
						<i class="icon icon-grid"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_WIDGETS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&view=items'); ?>">
						<i class="icon icon-pencil"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_CUSTOM_ITEMS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&view=groups'); ?>">
						<i class="icon icon-folder"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_GROUPS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&view=grids'); ?>">
						<i class="icon icon-grid-2" style="color: #db8ddb;"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_CUSTOM_GRIDS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_minitekwall&path=&return='.base64_encode(JURI::getInstance()->toString())); ?>">
						<i class="icon icon-cog"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_CONFIGURATION'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_minitekwall&task=widgets.deleteCroppedImages&'.JSession::getFormToken().'=1'); ?>">
						<i class="icon icon-trash" style="color: #ea7a7a;"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DELETE_CROPPED_IMAGES'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="https://extensions.joomla.org/extension/news-display/articles-display/minitek-wall-pro/" target="_blank">
						<i class="icon icon-star" style="color: #ffcb52;"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_LIKE_THIS_EXTENSION'); ?><br>
							<span class="small">
								<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_LEAVE_A_REVIEW_ON_JED'); ?>
							</span>
						</span>
					</a>
				</div>
			</div>
		</div>

		<div class="span4">

			<?php if (is_numeric($this->authEnabled) || !$this->authEnabled) { ?>
				<div class="dashboard-module download-id">
					<?php // Download ID missing 	
					if (is_numeric($this->authEnabled)) { ?>
						<div class="alert alert-danger">
							<h3><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD_ID_MISSING'); ?></h3>
							<p><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD_ID_DESC'); ?></p>
							<form action="<?php echo JRoute::_('index.php?option=com_minitekwall&view=dashboard'); ?>" method="post" name="adminForm" id="installAuthPlugin-form">
								<input type="text" name="downloadid" class="form-control inputbox" required placeholder="<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_ENTER_DOWNLOAD_ID'); ?>" />
								<div class="text-right">
									<button class="btn btn-info btn-sm mt-3" type="submit">
										<i class="fa fa-save"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SAVE'); ?>
									</button>
								</div>
								<input type="hidden" name="task" value="dashboard.saveDownloadId">
								<input type="hidden" name="id" value="<?php echo $this->authEnabled; ?>">
								<?php echo JHtml::_('form.token'); ?>
							</form>
						</div>
					<?php // Authentication plugin not installed
					} else if (!$this->authEnabled) { ?>
						<div class="alert alert-danger">
							<h3><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_AUTH_PLUGIN_MISSING'); ?></h3>
							<p><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_AUTH_PLUGIN_MISSING_DESC'); ?></p>	
							<a href="https://www.minitek.gr/downloads/minitek-updates-authentication" class="btn btn-success btn-sm" target="_blank">
								<i class="fa fa-download"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD'); ?>
							</a>
							<a href="https://www.minitek.gr/support/faq/products-updates/what-is-the-download-id" class="btn btn-info btn-sm" target="_blank">
								<i class="fa fa-info-circle"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_LEARN_MORE'); ?>
							</a>
						</div>
					<?php } ?>
				</div>
			<?php } ?>

			<div class="dashboard-module">
				<h2 class="nav-header"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_ABOUT'); ?></h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_EXTENSION'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/joomla/extensions/minitek-wall" target="_blank"><?php echo JText::_('COM_MINITEKWALL'); ?></a></div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_VERSION'); ?></div>
						<div class="span8">
							<span class="label label-default"><?php echo $local_version; ?></span>
							<a id="check-version" href="#" class="btn btn-info btn-small pull-right">
								<i class="fa fa-refresh"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_CHECK_VERSION'); ?>
							</a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_DEVELOPER'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/" target="_blank">Minitek</a></div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_LICENSE'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/terms-of-service" target="_blank">GNU GPLv3 Commercial</a></div>
					</div>
					<?php if ($this->authEnabled == 'active') { ?>
						<div class="row-fluid">
							<div class="span4"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_DOWNLOAD_ID'); ?></div>
							<div class="span8 text-success">
								<i class="fa fa-check-circle"></i>&nbsp;&nbsp;<?php echo JText::_('COM_MINITEKWALL_DASHBOARD_DOWNLOAD_ID_ACTIVE'); ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>

			<div class="dashboard-module">
				<h2 class="nav-header"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_QUICK_LINKS'); ?></h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-book" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/documentation/joomla/minitek-wall" target="_blank"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_DOCUMENTATION'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-list" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/changelogs/joomla/minitek-wall-pro" target="_blank"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_CHANGELOG'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-support" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/forum/joomla/minitek-wall" target="_blank"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_TECHNICAL_SUPPORT'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-help" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/documentation/joomla/minitek-wall/faq" target="_blank"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_SIDEBAR_FAQ'); ?></a>
							</span>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>
