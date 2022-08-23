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

<div id="joms-chatbar" class="joms-chatbar joms-chat__wrapper joms-chat--window" v-bind:class="position">
    <chatbar-sidebar></chatbar-sidebar>
    <div class="joms-chat__windows clearfix">
        <chatbar-window v-for="chat in openedChats"
            v-bind:key="chat.id"
            v-bind:data-id="chat.id"
            v-bind:chat="chat"></chatbar-window>
    </div>
</div>
