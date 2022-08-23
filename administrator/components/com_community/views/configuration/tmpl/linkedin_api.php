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
		<h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_API'); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/ben7cz" target="_blank"><i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_CLIENT_ID_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LINKEDIN_CLIENT_ID' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="linkedinclientid" value="<?php echo $this->config->get('linkedinclientid' , '' );?>" size="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_CLIENT_SECRET_TIPS'); ?>">
								<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_CLIENT_SECRET'); ?>
							</span>
						</td>
						<td>
							<input type="text" name="linkedinsecret" value="<?php echo $this->config->get('linkedinsecret' , '' );?>" size="50" />
						</td>
					</tr>
					<tr>
					<td class="key">
							<span>
								<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_REDIRECT_URL'); ?>
							</span>
						</td>
						<td>
							<pre><?php echo JURI::root().'index.php?option=com_community&view=frontpage&login=1&client_id=' . $this->config->get('linkedinclientid' , '' );?></pre>
						</td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_WATERMARK_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LINKEDIN_WATERMARK' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('linkedinwatermark' ,'ace-switch ace-switch-5', null , $this->config->get('linkedinwatermark') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LINKEDIN_REIMPORT_AVATAR_LOGIN_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LINKEDIN_REIMPORT_AVATAR_LOGIN' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('linkedinloginimportavatar' ,'ace-switch ace-switch-5', null , $this->config->get('linkedinloginimportavatar') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>