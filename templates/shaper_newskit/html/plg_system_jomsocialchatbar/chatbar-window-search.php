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

<div class="joms-chat__search">
    <span class="joms-input" title="Type your friend's name" v-on:click="onInputClick">
        <ul>
            <li v-for="user in this.selectedUsers">
                <span v-html="user.name"></span>
                <i class="fa fa-times" v-on:click.stop="removeSelected( user.id )"></i>
            </li>
            <li>
                <input class="joms-js-input" v-bind:style="{ width: inputWidth + 'px' }" v-on:input="onInputKeyup" />
            </li>
        </ul>
    </span>
    <span class="joms-input joms-input--shadow"></span>
    <div style="text-align:right; padding-top:3px">
        <button class="joms-button--neutral joms-button--smallest" v-on:click="cancel">
            <?php echo JText::_('COM_COMMUNITY_CANCEL'); ?>
        </button>
        <button class="joms-button--primary joms-button--smallest" v-on:click="add">
            <?php echo JText::_('COM_COMMUNITY_ADD'); ?>
        </button>
    </div>
    <div style="margin-top:10px">
        <div v-if="this.queryResults.length">
            <div class="joms-chat__item" v-for="user in this.queryResults"
                    v-on:click.stop="onSelect( user.id )">
                <div class="joms-avatar" v-bind:class="[ user.online ? 'joms-online' : '' ]">
                    <a><img v-bind:src="user.avatar" /></a>
                </div>
                <div class="joms-chat__item-body" v-html="user.name"></div>
            </div>
        </div>
    </div>
</div>
