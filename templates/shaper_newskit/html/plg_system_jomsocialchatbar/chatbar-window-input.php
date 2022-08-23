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

<div class="joms-chat__input-container">
    <div class="joms-chat__input-wrapper">
        <div v-if="attachment" class="joms-chat__input-preview">
            <img v-if="attachment.type === 'image'" v-bind:src="attachment.url" alt="placeholder" />
            <div v-if="attachment.type === 'file'"><strong>{{ attachment.name }}</strong></div>
            <i class="fa fa-times" v-on:click.prevent.stop="removeAttachment"></i>
        </div>
        <textarea 
            rows="1" 
            placeholder="<?php echo JText::_('COM_COMMUNITY_CHAT_TYPE_YOUR_MESSAGE_HERE'); ?>" 
            v-on:keydown.enter.exact.prevent
            v-on:keyup.enter.exact="submit"
            v-bind:disabled="chat.blocked" ></textarea>
        <div class="joms-chat__input-actions" v-if="!chat.blocked">
            <?php
              $message_file_sharing =   CFactory::getConfig()->get('message_file_sharing');
              if($message_file_sharing):
            ?>
            <a href="#" v-on:click.prevent.stop="attachFile">
                <i class="fa fa-file-archive-o"></i>
            </a>
            <?php 
            endif;
            ?>
            <a href="#" v-on:click.prevent.stop="attachImage">
                <i class="fa fa-camera"></i>
            </a>
        </div>
    </div>
</div>
