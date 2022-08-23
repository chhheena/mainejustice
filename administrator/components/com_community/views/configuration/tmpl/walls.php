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
		<h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS'); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REACTIONS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_WALL_REACTIONS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enablewalllikereaction' ,'ace-switch ace-switch-5', null , $this->config->get('enablewalllikereaction') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_COMMENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_COMMENTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enablewallcomment' ,'ace-switch ace-switch-5', null , $this->config->get('enablewallcomment') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EDIT_COMMENTS_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EDIT_COMMENTS_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('wallediting' ,'ace-switch ace-switch-5', null , $this->config->get('wallediting') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockprofilewalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockprofilewalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_VIDEO_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_VIDEO_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockvideoswalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockvideoswalls') ); ?>
						</td>
					</tr>
					<tr>
						<td width="300" class="key">
							<span class="hasTip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_PHOTO_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_PHOTO_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td valign="top">
							<?php /*echo JHTML::_('select.booleanlist' , 'lockphotoswalls' , null , $this->config->get('lockphotoswalls') , JText::_('COM_COMMUNITY_YES_OPTION') , JText::_('COM_COMMUNITY_NO_OPTION') ); */?>
							<?php echo CHTMLInput::checkbox('lockphotoswalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockphotoswalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_PAGE_WALLS_TO_MEMBERS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_PAGE_WALLS_TO_MEMBERS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockpagewalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockpagewalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_GROUP_WALLS_TO_MEMBERS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_GROUP_WALLS_TO_MEMBERS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockgroupwalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockgroupwalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_EVENT_WALLS_TO_RECIPIENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_EVENT_WALLS_TO_RECIPIENTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockeventwalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockeventwalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_AUTO_REFRESH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_AUTO_REFRESH' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enable_refresh' ,'ace-switch ace-switch-5', null , $this->config->get('enable_refresh') ); ?>
						</td>
					</tr>
                    <tr>
                        <td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_INTERVAL_TIME_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_INTERVAL_TIME' ); ?>
							</span>
                        </td>
                        <td>
                        	<select name="stream_refresh_interval">
	                            <?php

	                            $options = array(1000, 2000, 3000, 5000, 10000, 20000, 30000, 60000);
	                            $optionLabels = array('COM_COMMUNITY_1_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_2_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_3_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_5_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_10_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_20_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_30_SEC_AJAX_INTERVAL', 'COM_COMMUNITY_60_SEC_AJAX_INTERVAL');
	                            $selectedValue = (int) $this->config->get('stream_refresh_interval');
	                            $selectedValue = in_array($selectedValue, $options) ? $selectedValue : (int) $this->config->get('stream_refresh_interval' );
	                            foreach ($options as $index => $value) {
	                                echo '<option value="' . $value . '"' . ($value == $selectedValue ? ' selected' : '') .
	                                        '>' . JText::_($optionLabels[$index]) . '</option>';
	                            }

	                            ?>
	                        </select>
                        </td>
                    </tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_DEFAULT_COMMENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_DEFAULT_COMMENTS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="stream_default_comments" value="<?php echo $this->config->get('stream_default_comments');?>" size="4" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_COMMENTS_MORE_LENGTH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_COMMENTS_MORE_LENGTH' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="prev_comment_load" value="<?php echo $this->config->get('prev_comment_load');?>" size="4" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_COMMENTS_LENGTH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_COMMENTS_LENGTH' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="stream_comment_length" value="<?php echo $this->config->get('stream_comment_length');?>" size="4" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>