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
		<h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_API'); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/cbn7cz" target="_blank"><i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_API_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_TWITTER_API_KEY' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="twitterconnectkey" value="<?php echo $this->config->get('twitterconnectkey' , '' );?>" size="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_APPLICATION_SECRET_TIPS'); ?>">
								<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_APPLICATION_SECRET'); ?>
							</span>
						</td>
						<td>
							<input type="text" name="twitterconnectsecret" value="<?php echo $this->config->get('twitterconnectsecret' , '' );?>" size="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_APPLICATION_CALLBACK_URL_TIPS'); ?>">
								<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_APPLICATION_CALLBACK_URL'); ?>
							</span>
						</td>
						<td>
							<pre><?php echo JUri::root() . 'index.php/component/community/oauth/callback/twitter/login'; ?></pre>
						</td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_WATERMARK_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_TWITTER_WATERMARK' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('twitterwatermark' ,'ace-switch ace-switch-5', null , $this->config->get('twitterwatermark') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_REIMPORT_PROFILE_LOGIN_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_TWITTER_REIMPORT_PROFILE_LOGIN' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('twitterloginimportprofile' ,'ace-switch ace-switch-5', null , $this->config->get('twitterloginimportprofile') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_TWITTER_REIMPORT_AVATAR_LOGIN_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_TWITTER_REIMPORT_AVATAR_LOGIN' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('twitterloginimportavatar' ,'ace-switch ace-switch-5', null , $this->config->get('twitterloginimportavatar') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>