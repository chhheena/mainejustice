<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die; 
?>

<div class="joms-chat__window" v-bind:class="{ inactive: !active, unread: chat.seen === 0, 'joms-chat__initializing': initData }" >
    <div>
        <div class="joms-chat__window-title" v-on:click.prevent.stop="toggle">
            <span class="joms-chat__status" ></span>
            <span class="joms-chat__title" v-html="chat.name"></span>

            <?php if(JFactory::getLanguage()->isRTL()) { ?>
                <div style="position:absolute; top:4px; left:10px">
            <?php } else { ?>
                <div style="position:absolute; top:4px; right:10px">
            <?php } ?>
                <a href="#" v-on:click.prevent.stop="toggleSetting(); setActive( chat )" v-show="chat.open == 1">
                    <i class="fa fa-cog"></i>
                </a>
                <a href="#" v-on:click.prevent.stop="close">
                    <i class="fa fa-window-close"></i>
                </a>
            </div>
        </div>
        <div class="joms-chat__window-body" v-show="chat.open == 1" style="position:relative" v-on:click="toggleSetting('hide');setActive( chat )">
            <ul class="joms-dropdown" v-if="setting"
                    style="display:block; position:absolute; top:0; left:0; right:0">
                <li><a href="#" v-on:click.prevent.stop="mute( ! ( +chat.mute ) )">
                    <span v-if="+chat.mute"><?php echo JText::_('COM_COMMUNITY_CHAT_UNMUTE') ?></span>
                    <span v-else><?php echo JText::_('COM_COMMUNITY_CHAT_MUTE') ?></span></a>
                </li>
                <li><a href="#" v-on:click.prevent.stop="leave">
                    <?php echo JText::_('COM_COMMUNITY_CHAT_LEAVE_CHAT') ?></a>
                </li>
            </ul>
            <div v-if="adding" style="display:block; position:absolute; top:0; left:0; right:0">
                <chatbar-window-search
                    v-bind:participants="participants"
                    v-on:done="addUsers"
                    v-on:hide="addUsers"></chatbar-window-search>
            </div>
            <div class="joms-chat__message">
                <div class="joms-chat__scrollable joms-js-scrollable" v-on:scroll="handleScroll" v-on:wheel="handleWheel" style="height:250px; overflow:auto;">
                    <div v-bind:style="{ padding: '5px', textAlign: 'center', visibility: loading ? 'visible' : 'hidden' }">
                        <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" />
                    </div>
                    <div v-for="dgroup in dgroups"
                            v-bind:key="dgroup.date"
                            v-bind:data-id="dgroup.date"
                            class="joms-chat__messages">
                        <div class="joms-chat__message-item" style="text-align:center">
                            <small><strong>{{ dgroup.dateFormatted }}</strong></small>
                        </div>
                        <div>
                            <div class="joms-chat__message-item" v-for="ugroup in dgroup.messages"
                                    v-bind:data-id="ugroup.user">
                                <div v-if="ugroup.info === 'add'" v-for="message in ugroup.messages"
                                        v-bind:data-tooltip="message.timeFormatted"
                                        v-on:mouseenter="showTooltip" v-on:mouseleave="hideTooltip"
                                        style="text-align:center; padding:0 10px; line-height:1em">
                                    <small v-if="String( message.user_id ) === String( myId )">
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_MSG_YOU_ADDED'); ?>
                                    </small>
                                    <small v-else>
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_MSG_ADDED', '<span v-html="getName( message.user_id )"></span>', ''); ?>
                                    </small>
                                </div>
                                <div v-if="ugroup.info === 'change_chat_name'" v-for="message in ugroup.messages"
                                        v-bind:data-tooltip="message.timeFormatted"
                                        v-on:mouseenter="showTooltip" v-on:mouseleave="hideTooltip"
                                        style="text-align:center; padding:0 10px; line-height:1em">
                                    <small v-if="String( message.user_id ) === String( myId )">
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_YOU_CHANGE_GROUP_NAME', '{{ message.params.groupname }}'); ?>
                                    </small>
                                    <small v-else>
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_GROUP_NAME_HAS_CHANGED', '{{ getName( message.user_id ) }}', '{{ message.params.groupname }}'); ?>
                                    </small>
                                </div>
                                <div v-if="ugroup.info === 'leave'" v-for="message in ugroup.messages"
                                        v-bind:data-tooltip="message.timeFormatted"
                                        v-on:mouseenter="showTooltip" v-on:mouseleave="hideTooltip"
                                        style="text-align:center; padding:0 10px; line-height:1em">
                                    <small v-if="String( message.user_id ) === String( myId )">
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_MSG_YOU_LEAVE'); ?>
                                    </small>
                                    <small v-else>
                                        <?php echo JText::sprintf('COM_COMMUNITY_CHAT_MSG_LEAVE', '<span v-html="getName( message.user_id )"></span>'); ?>
                                    </small>
                                </div>
                                <div v-if="ugroup.user">
                                    <small><strong></strong><span v-html="getName( ugroup.user )"></span></small>
                                    <div v-bind:class="{
                                        'joms-chat__message-body': true,
                                        'joms-chat__message-body--mine': String( ugroup.user ) === String( myId )
                                    }">
                                        <div v-for="message in ugroup.messages" v-bind:data-id="message.id"
                                                v-bind:data-tooltip="message.timeFormatted"
                                                v-on:mouseenter="showTooltip" v-on:mouseleave="hideTooltip"
                                                v-bind:class="{'joms-chat__message-error': message.error}">
                                            <span class="joms-chat__message-content"
                                                v-html="replaceEmoticon( replaceNewline( replaceLink( message.content ) ) )"></span>
                                            <div class="joms-chat__attachment-image"
                                                v-if="message.attachment && message.attachment.type === 'image'">
                                                <a href="#" v-on:click.prevent="photoZoom( message.attachment.url )">
                                                    <img width="150px" height="150px" v-bind:src="message.attachment.url" alt="photo thumbnail" />
                                                </a>
                                            </div>
                                            <div class="joms-chat__attachment-file"
                                                v-if="message.attachment && message.attachment.type === 'file'">
                                                <i class="fa fa-file"></i>
                                                <a v-bind:href="message.attachment.url" v-bind:title="message.attachment.url"
                                                    target="_blank" style="float:none">
                                                    <strong>{{ message.attachment.name }}</strong>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-show="seenBy.length"
                        class="joms-chat__messages-seen"
                        style="float:right; margin-right: 15px">
                        <span class="joms-chat__messages-seen_status"
                            v-bind:data-tooltip="seenUsers"
                            v-on:mouseenter="showTooltip" 
                            v-on:mouseleave="hideTooltip">{{ seenText }}</span>
                    </div>
                </div>
            </div>
        </div>
        <chatbar-window-input 
            v-show="chat.open == 1"
            v-bind:chat="chat"
            v-bind:active="active"
            v-on:submit="submit"
            v-on:click.native="toggleSetting( 'hide' ); setActive( chat )"></chatbar-window-input>
    </div>
</div>
