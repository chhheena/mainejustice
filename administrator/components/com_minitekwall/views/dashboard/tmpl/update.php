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
$latest_version = $this->utilities->latestVersion();
$type = JFactory::getApplication()->input->get('type', 'auto');

if ($latest_version && version_compare($latest_version, $local_version, '>')) { ?>
  <div class="alert alert-success update-box">
    <div class="update-info">
      <div style="margin: 0 0 10px;"><span><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_A_NEW_VERSION_IS_AVAILABLE'); ?></span></div>
      <div>
        <span class="label label-success"><?php echo $latest_version; ?></span>
        <a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_installer&view=update'); ?>"><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_UPDATE_NOW'); ?></a>
      </div>
    </div>
  </div>
<?php } else if ($type == 'check') { ?>
  <?php if ($latest_version) { ?>
    <div class="alert alert-success update-box">
      <div class="update-info">
        <div>
          <?php if ($latest_version == $local_version) { ?>
            <span><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_YOU_HAVE_THE_LATEST_VERSION'); ?></span>
          <?php } else { ?>
            <span><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_YOU_HAVE_A_DEVELOPMENT_VERSION'); ?></span>
          <?php } ?>
          <span class="label label-success"><?php echo $latest_version; ?></span>
        </div>
      </div>
    </div>
  <?php } else { ?>
    <div class="alert alert-danger update-box">
      <div class="update-info">
        <div>
          <span><?php echo JText::_('COM_MINITEKWALL_DASHBOARD_COULD_NOT_FETCH_UPDATE_INFO'); ?></span>
        </div>
      </div>
    </div>
  <?php } ?>
<?php }
