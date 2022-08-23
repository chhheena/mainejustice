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
		
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<fieldset class="adminform">
				<table>
					<tbody>
						<tr>
							<td width="250" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablepages' ,'ace-switch ace-switch-5', null , $this->config->get('enablepages') ); ?>
							</td>
						</tr>
						<tr>
							<td width="350" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_ALLOW_GUEST_SEARCH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_ALLOW_GUEST_SEARCH' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableguestsearchpages' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestsearchpages') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_MODERATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_MODERATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('moderatepagecreation' ,'ace-switch ace-switch-5', null , $this->config->get('moderatepagecreation') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_ALLOW_CREATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_ALLOW_CREATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createpages' ,'ace-switch ace-switch-5', null , $this->config->get('createpages') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_CREATION_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_CREATION_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="pagecreatelimit" value="<?php echo $this->config->get('pagecreatelimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_PHOTOS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_PHOTOS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('pagephotos' ,'ace-switch ace-switch-5', null , $this->config->get('pagephotos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_PHOTO_UPLOAD_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_PHOTO_UPLOAD_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="pagephotouploadlimit" value="<?php echo $this->config->get('pagephotouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_VIDEOS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_VIDEOS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('pagevideos' ,'ace-switch ace-switch-5', null , $this->config->get('pagevideos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_VIDEO_UPLOAD_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_VIDEO_UPLOAD_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="pagevideouploadlimit" value="<?php echo $this->config->get('pagevideouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_EVENTS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_EVENTS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('page_events' ,'ace-switch ace-switch-5', null , $this->config->get('page_events') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_POLLS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_POLLS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('page_polls' ,'ace-switch ace-switch-5', null , $this->config->get('page_polls') ); ?>
							</td>
						</tr>
						<!-- <tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_ANNOUNCEMENTS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_ANNOUNCEMENTS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createpageannouncement' ,'ace-switch ace-switch-5', null , $this->config->get('createpageannouncement') ); ?>
							</td>
						</tr> -->
						<!-- <tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSIONS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSIONS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createpagediscussion' ,'ace-switch ace-switch-5', null , $this->config->get('createpagediscussion') ); ?>
							</td>
						</tr> -->
						<!-- <tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_NOTIFICATIONS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_NOTIFICATIONS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('pagediscussnotification' ,'ace-switch ace-switch-5', null , $this->config->get('pagediscussnotification') ); ?>
							</td>
						</tr> -->
						<tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_PAGES_SHOW_FILESHARING_TIPS')?>">
			                        <?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_PAGES_SHOW_FILESHARING')?>
			                    </span>
			                </td>
			                <td>
			                	<?php echo CHTMLInput::checkbox('file_sharing_page' ,'ace-switch ace-switch-5', null , $this->config->get('file_sharing_page') ); ?>
			                </td>
			            </tr>
						<!-- <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING' ); ?>
			                     </span>
			                 </td>
			                 <td>
			                 	<?php echo CHTMLInput::checkbox('pagediscussfilesharing' ,'ace-switch ace-switch-5', null , $this->config->get('pagediscussfilesharing') ); ?>
							</td>
			            </tr> -->
			            <!-- <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_PAGES_BULLETIN_FILE_SHARING_TIPS')?>">
			                        <?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_PAGES_BULLETIN_FILE_SHARING')?>
			                    </span>
			                </td>
			                <td>
			                	<?php echo CHTMLInput::checkbox('pagebulletinfilesharing' ,'ace-switch ace-switch-5', null , $this->config->get('pagebulletinfilesharing') ); ?>
			                </td>
			            </tr> -->
			            <!-- <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING_LIMIT_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING_LIMIT' ); ?>
			                    </span>
			                </td>
			                <td>
								<input type="text" name="pagediscussionfilelimit" value="<?php echo $this->config->get('pagediscussionfilelimit' );?>" size="8" />
							</td>
			            </tr> -->
			            <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING_MAX_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_DISCUSSION_FILE_SHARING_MAX_SIZE' ); ?>
			                    </span>
			                </td>
			                <td>
								<input type="text" name="pagefilemaxuploadsize" value="<?php echo $this->config->get('pagefilemaxuploadsize' );?>" size="8" /> (MB)
								<div><?php echo JText::sprintf('COM_COMMUNITY_CONFIGURATION_PHOTOS_MAXIMUM_UPLOAD_SIZE_FROM_PHP', $this->uploadLimit );?></div>
							</td>
			             </tr>
			             <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PAGES_FILE_SHARING_EXT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PAGES_FILE_SHARING_EXT' ); ?>
			                    </span>
			                </td>
			                <td>
								<input type="text" name="file_sharing_page_ext" value="<?php echo $this->config->get('file_sharing_page_ext' );?>" size="8" />
							</td>
			             </tr>
			            <tr>
		                    <td class="key">
		                        <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_PERUPLOAD_FILESHARING_TIPS'); ?>">
		                        <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_PERUPLOAD_FILESHARING' ); ?>
		                        </span>
		                    </td>
		                    <td valign="top">
		                        <input type="text" name="file_sharing_limit_per_upload_page" value="<?php echo $this->config->get('file_sharing_limit_per_upload_page', 10);?>" size="4" />
		                    </td>
		                </tr>
					</tbody>
				</table>
			</fieldset>


		</div>
	</div>
</div>





