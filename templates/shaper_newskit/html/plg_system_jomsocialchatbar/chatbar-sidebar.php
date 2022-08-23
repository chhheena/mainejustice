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

<div id="joms-chatbar-sidebar" class="" class="joms-chat__window">
    <div class="joms-chat__window-title" v-on:click.prevent.stop="toggle">
        <?php echo JText::_('COM_COMMUNITY_CHAT_CONVERSATIONS'); ?>
        <a href="#" class="joms-chat__window-close">
            <i class="fa" v-bind:class="[ expanded ? 'fa-window-minimize' : 'fa-window-maximize' ]"></i>
        </a>
    </div>
    <div class="joms-chat__conversations" v-show="expanded" v-on:wheel="handleScroll">
        <div v-for="chat in this.chats"
            v-bind:key="chat.id"
            v-bind:data-id="chat.id"
            v-on:click.stop.prevent="open(chat.id);setActiveWindow( chat );"
            v-bind:class="{ unread: chat.seen === 0 }"
            class="joms-chat__item"
        >
            <div v-bind:class="{ 'joms-avatar': true, 'joms-online': chat.online }">
                <a><img v-bind:src="chat.thumb" /></a>
            </div>
            <div class="joms-chat__item-body">
                <span v-if="+chat.mute" style="float:right;">
                    <i class="fa fa-bell-slash"></i>
                </span>
                <strong v-html="chat.name"></strong>
            </div>
        </div>
        <div v-if="fetching" style="padding:5px; text-align:center">
            <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" />
        </div>
        <div v-if="fetchDone" style="padding:5px 10px; font-style:italic; font-weight:bold; color:rgba(0,0,0,.4)">
            <small><?php echo JText::_('COM_COMMUNITY_CHAT_NO_MORE_CONVERSATIONS'); ?></small>
        </div>
    </div>
</div>
