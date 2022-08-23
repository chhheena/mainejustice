<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
echo CommunityLicenseHelper::disabledHtml();
?>

<style>
    .container-main {
        padding-bottom: 0 !important;
    }
</style>
<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::script('administrator/components/com_community/assets/js/tab.js');
JHtml::script('administrator/components/com_community/assets/js/js.cookie.min.js');

$cookie = JFactory::getApplication()->input->cookie;
$cookiePath = md5(JUri::getInstance());
JFactory::getDocument()->addScriptOptions('cookiePath', $cookiePath);

$activeTab = $cookie->getString('myTab-' . $cookiePath, '#general', '');
?>
<!-- Tabs header -->
<ul id="myTab" class="nav nav-tabs">
    <!-- System requirement -->
    <li class="<?php echo $activeTab === '#general' ? 'active' : '' ?>">
        <a href="#general"><?php echo JText::_('COM_COMMUNITY_THEMEPROFILE_GENERAL_TAB'); ?></a>
    </li>
    <li class="<?php echo $activeTab === '#info' ? 'active' : '' ?>">
        <a href="#info"><?php echo JText::_('COM_COMMUNITY_THEMEPROFILE_USER_INFO_TAB'); ?></a>
    </li>
    <li class="<?php echo $activeTab === '#badge' ? 'active' : '' ?>">
        <a href="#badge"><?php echo JText::_('COM_COMMUNITY_THEMEPROFILE_USER_NAME_BADGE'); ?></a>
    </li>
</ul>
<!-- Tabs content -->
<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
<div id="myTabContent" class="tab-content" style="padding-top:24px;">
    <!-- general -->
    <div class="tab-pane <?php echo $activeTab === '#general' ? 'active' : '' ?> in" id="general">
        <?php include_once JPATH_ROOT . '/administrator/components/com_community/views/themeprofile/tmpl/general.php'; ?>
    </div>
    <!-- user information -->
    <div class="tab-pane <?php echo $activeTab === '#info' ? 'active' : '' ?>" id="info">
        <?php include_once JPATH_ROOT . '/administrator/components/com_community/views/themeprofile/tmpl/info.php'; ?>
    </div>
    <!-- user name badge -->
    <div class="tab-pane <?php echo $activeTab === '#badge' ? 'active' : '' ?>" id="badge">
        <?php include_once JPATH_ROOT . '/administrator/components/com_community/views/themeprofile/tmpl/badge.php'; ?>
    </div>
</div>
<input type="hidden" name="view" value="themeprofile" />
<input type="hidden" name="task" value="apply" />
<input type="hidden" name="option" value="com_community" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script>
jQuery( document ).ready(function($) {
    initJomsTab($('#myTab'), 'myTab');

    Joomla.submitbutton = function( action ) {
        if ( action === 'apply' && !checkFiles() ) {
            window.alert('<?php echo JText::_("COM_COMMUNITY_THEME_IMAGE_ERROR"); ?>')
            return false;
        }
        Joomla.submitform(action);
    }

    function checkFiles() {
        var isValid = true;
        $('#adminForm input[type=file]').each(function() {
            if ( this.value && !this.value.match(/\.(jpg|jpeg|png)$/i) ) {
                isValid = false;
                return false;
            }
        });
        return isValid;
    }

    $('#default-cover-new,#default-cover-female-new,#default-cover-male-new,#default-male-avatar-new,#default-female-avatar-new,#default-general-avatar-new,.profile-group-badge').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false
    });

});
</script>

<style type="text/css">

    .preview-cover {
        max-width: 100%;
        height: 150px;
    }

    .preview-avatar {
        max-width: 100%;
        height: 150px;
    }


</style>
