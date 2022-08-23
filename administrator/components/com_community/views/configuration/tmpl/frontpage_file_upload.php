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
?>

<div class="widget-box">
    <div class="widget-header widget-header-flat">
        <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FILESHARING_FRONTPAGE'); ?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table>
                <tbody>
                    <tr>
                        <td class="key" width="200">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_FILESHARING_TIPS'); ?>">
                            <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_FILESHARING'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <?php echo CHTMLInput::checkbox('file_sharing_activity' ,'ace-switch ace-switch-5', null , $this->config->get('file_sharing_activity') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_MAX_FILESHARING_TIPS'); ?>">
                            <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_MAX_FILESHARING' ); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="file_sharing_activity_max" value="<?php echo $this->config->get('file_sharing_activity_max', 0);?>" size="4" /> (MB)
                            <div><?php echo JText::sprintf('COM_COMMUNITY_CONFIGURATION_PHOTOS_MAXIMUM_UPLOAD_SIZE_FROM_PHP', $this->uploadLimit );?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_EXTENSION_FILESHARING_TIPS'); ?>">
                            <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_EXTENSION_FILESHARING' ); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="file_sharing_activity_ext" value="<?php echo $this->config->get('file_sharing_activity_ext', 0);?>" size="4" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_PERUPLOAD_FILESHARING_TIPS'); ?>">
                            <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_PERUPLOAD_FILESHARING' ); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="file_sharing_limit_per_upload" value="<?php echo $this->config->get('file_sharing_limit_per_upload', 10);?>" size="4" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
