<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

use Joomla\CMS\HTML\HTMLHelper;

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
HTMLHelper::_('bootstrap.framework');
HTMLHelper::script('components/com_community/assets/jqueryui/jquery-ui.min.js');
HTMLHelper::script(COMMUNITY_ASSETS_URL . '/js/bootstrap.min.js');
HTMLHelper::script(COMMUNITY_ASSETS_URL . '/js/ace-elements.min.js');
HTMLHelper::script(COMMUNITY_ASSETS_URL . '/js/ace.min.js');
HTMLHelper::script('components/com_community/assets/pickadate/picker.combined.js');
HTMLHelper::stylesheet('components/com_community/assets/pickadate/themes/classic.combined.css');
?>

<!-- pickadate.js -->
<style>.picker__select--month{width:35% !important}.picker__select--year{width:22.5% !important}</style>

<div id="js-cpanel" class="row">
    <div class="navbar">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a href="<?php echo JRoute::_('index.php?option=com_community'); ?>" class="brand">
            <small><?php echo JText::_('COM_COMMUNITY_CPANEL')?></small>
          </a><!--/.brand-->
            <?php
                $communityController = new CommunityController();
                $localVersion =  $communityController->_getLocalVersionNumber();
            ?>
            <span id="jomsocial-version" class="badge badge-important"><?php echo $localVersion; ?></span>

          <ul class="nav ace-nav pull-right">

            <li>
              <a class="js-noti-dropdown-toggle" data-toggle="dropdown" href="javascript:;">
                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS')?>
                <i class="js-icon-bell-alt"></i>
                <span class="badge badge-important"><?php echo $this->total; ?></span> <!-- show the total reports -->
              </a>
            <?php if($this->total) { ?>
                <ul class="pull-right dropdown-navbar navbar-js dropdown-menu dropdown-caret dropdown-closer">
                    <li class="nav-header">
                      <i class="joms-icon-warning-sign"></i>
                      <?php echo $this->total. ' '. JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS'); ?>
                    </li>

                    <!-- Don't show the empty report -->
                    <?php if($this->pendingGroup){?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=groups&status=0')?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_GROUPS_PENDING')?>
                                </span>
                                <span class="pull-right orange"><?php echo $this->pendingGroup; ?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->pendingEvent){?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=events&status=0')?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_EVENTS_PENDING')?>
                                </span>
                                <span class="pull-right orange"><?php echo $this->pendingEvent; ?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->pendingUser){?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=users&usertype=jomsocial&status=1&usesearch=0'); ?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_MEMBERS_PENDING')?>
                                </span>
                                <span class="pull-right orange"><?php echo $this->pendingUser;?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->pendingRequestInvite){?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=pendinginvites&search=&status=0'); ?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_PENDING_INVITATIONS')?>
                                </span>
                                <span class="pull-right orange"><?php echo $this->pendingRequestInvite;?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->reportCount){ ?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=reports&status=0'); ?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_REPORTS')?>
                                </span>
                                <span class="pull-right orange"><?php echo $this->reportCount; ?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->unsendCount){?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_community&view=mailqueue&status=0')?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_MAIL_UNSENT')?>
                                </span>
                                <span class="pull-right red"><?php echo $this->unsendCount; ?></span>
                            </div>
                        </a>
                    </li>
                    <?php }?>
                    <?php if($this->version){?>
                    <!-- <li>
                        <a href="<?php echo (empty($this->versionUrl)) ? '#' : $this->versionUrl; ?>">
                            <div class="clearfix">
                                <span class="pull-left">
                                    <?php echo JText::_('COM_COMMUNITY_NEW_VERSION')?>
                                </span>
                                <span class="pull-right green"><?php echo $this->version ?></span>
                            </div>
                        </a>
                    </li> -->
                    <?php }?>
                </ul>
            <?php }?>


            </li>

            <li>
                <a href="#">
                    <img src="<?php echo $this->my->getThumbAvatar(); ?>" alt="" class="inline avatar-topbar img-circle">
                    <span><?php echo JText::sprintf('COM_COMMUNITY_TOOLBAR_GREETING',$this->my->getDisplayName())?></span>
                </a>
            </li>

          </ul><!--/.ace-nav-->
        </div><!--/.container-fluid-->
      </div><!--/.navbar-inner-->
    </div>

    <div class="main-container container-fluid">
      <a class="menu-toggler" id="menu-toggler" href="#">
        <span class="menu-text"></span>
      </a>

      <div class="sidebar" id="sidebar">

        <?php echo $this->getSideMenuHTML(); ?>

        <div class="sidebar-collapse" id="sidebar-collapse">
          <i class="js-icon-double-angle-left"></i>
        </div>
      </div>

      <div class="main-content">

        <div class="page-content">
            <div class="page-header clearfix no-padding">
                <h1 class="pull-left"><?php echo $this->pageTitle; ?></h1>
            </div>
            <?php echo $this->pageContent; ?>
        </div><!--/.page-content-->

      </div><!--/.main-content-->
</div> <!-- #js-cpanel end -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // move the Joomla button toolbar to the layout
    var $toolbarClone = jQuery("#toolbar").clone();
    $toolbarClone.addClass("pull-right no-margin").prependTo(".page-header");

    // Tooltips

    jQuery(".js-tooltip, .hasTooltip").tooltip({
        html: true
    });

    // Apply class to doc buttons @TODO: probably can be achieved with JButton
    jQuery('span.icon-help').parent().addClass('btn-doc');

    jQuery('.js-noti-dropdown-toggle').dropdown();

    jQuery('div.pagination div.limit:first').remove();

    <?php
    $jinput = JFactory::getApplication()->input;
    if($jinput->get('view')) {?>
    if (window.MooTools) (function($) {
    // fix for Bootstrap Tooltips - conflicting with mootools-more
    $$('.js-tooltip, .hasTooltip').each(function (e) {
        e.hide = null;
    });
    })(MooTools);
    <?php }?>
});
</script>
