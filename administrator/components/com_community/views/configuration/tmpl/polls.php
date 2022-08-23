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
		<h5>&nbsp;</h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<fieldset class="adminform">
				<table>
					<tbody>
						<tr>
							<td width="250" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_POLLS_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_POLLS_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablepolls' ,'ace-switch ace-switch-5', null , $this->config->get('enablepolls') ); ?>
							</td>
						</tr>
						<tr>
							<td width="350" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_POLLS_ALLOW_GUEST_SEARCH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_POLLS_ALLOW_GUEST_SEARCH' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableguestsearchpolls' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestsearchpolls') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_POLLS_MODERATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_POLLS_MODERATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('moderatepollcreation' ,'ace-switch ace-switch-5', null , $this->config->get('moderatepollcreation') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_POLLS_ALLOW_CREATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_POLLS_ALLOW_CREATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createpolls' ,'ace-switch ace-switch-5', null , $this->config->get('createpolls') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_POLLS_CREATION_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_POLLS_CREATION_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="pollcreatelimit" value="<?php echo $this->config->get('pollcreatelimit' );?>" size="10" />
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
	</div>
</div>





