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
        <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS_AJAX'); ?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table>
                <tbody>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS_AJAX_AUTO_REFRESH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS_AJAX_AUTO_REFRESH' ); ?>
							</span>
                    </td>
                    <td>
                        <?php echo CHTMLInput::checkbox('notifications_ajax_enable_refresh' ,'ace-switch ace-switch-5', null , $this->config->get('notifications_ajax_enable_refresh') ); ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS_AJAX_INTERVAL_TIME_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_NOTIFICATIONS_AJAX_INTERVAL_TIME' ); ?>
							</span>
                    </td>
                    <td>
                        <select name="notifications_ajax_refresh_interval">
                            <?php

                            $options = array(1000, 2000, 3000, 5000, 10000, 20000, 30000, 60000);
                            $optionLabels = array('COM_COMMUNITY_1_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_2_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_3_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_5_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_10_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_20_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_30_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_60_SEC_AJAX_INTERVAL');
                            $selectedValue = (int) $this->config->get('notifications_ajax_refresh_interval');
                            $selectedValue = in_array($selectedValue, $options) ? $selectedValue : (int) $this->config->get('notifications_ajax_refresh_interval' );
                            foreach ($options as $index => $value) {
                                echo '<option value="' . $value . '"' . ($value == $selectedValue ? ' selected' : '') .
                                        '>' . JText::_($optionLabels[$index]) . '</option>';
                            }

                            ?>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>