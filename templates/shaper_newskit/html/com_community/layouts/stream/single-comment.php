<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();
$enablereaction = $config->get('enablereaction');

$canReport = false;
$stream_id = 0; 
                
if ($wall->stream_id) {
    $stream_id = $wall->stream_id;
} else if ($activityId) {
    $stream_id = $activityId;
}

if ($stream_id && CFactory::getConfig()->get('enablereporting') && (($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) || ($my->id > 0 && $my->id != $wall->post_by)) && !COwnerHelper::isCommunityAdmin() ) {
    $canReport = true;
}
?>

<div class="joms-comment__item joms-js--comment joms-js--comment-<?php echo $wall->id; ?>" data-id="<?php echo $wall->id; ?>" data-parent="<?php echo $wall->contentid; ?>" data-comment-id="<?php echo $wall->id; ?>" data-stream-id="<?php echo $wall->stream_id ?>">
    <div class="joms-comment__header">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>">
            </a>
        </div>
        <div class="joms-comment__body joms-js--comment-body">
            <a class="joms-comment__user" href="<?php echo CUrlHelper::userLink($user->id); ?>" id="wall-id-<?php echo $wall->id; ?>"><?php echo $user->getDisplayName(false, true); ?></a>
            <span class="joms-js--comment-content"><?php
                echo CActivities::shorten($wall->comment, $wall->id, 0, $config->getInt('stream_comment_length'),'comment');
                ?></span>
            <?php if ( !empty( $photoThumbnail ) ) { ?>
            <div style="padding: 5px 0">
                <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $photoThumbnail; ?>');">
                    <img class="joms-stream-thumb" src="<?php echo $photoThumbnail; ?>" alt="photo thumbnail" >
                </a>
            </div>
            <?php } else if ($paramsHTML) { ?>
            <?php echo $paramsHTML; ?>
            <?php } ?>

            <span class="joms-comment__time"><small><?php echo CTimeHelper::timeLapse($date); ?></small></span>
            
            <?php if ($my->id || $canReport): ?>
            <div class="joms-comment__actions joms-js--comment-actions">
            <?php if ($enablereaction): ?>
                <?php if ($config->get('enablewalllikereaction')) { ?>
                    <?php echo CLikesHelper::renderReactionButton('comment', '', $wall->id, $reactedId); ?>
                    <div class="joms-comment__reaction-status">
                        <?php echo CLikesHelper::commentRenderReactionStatus($wall->id); ?>
                    </div>
                <?php } ?>

                <?php if($canEdit || $canRemove || $canReport): ?>
                <div class="joms-list__options">
                    <a href="javascript:" class="joms-button--options" data-ui-object="joms-dropdown-button" data-type="no-popup">
                        &#8226;&#8226;&#8226;
                    </a>
                    <ul class="joms-dropdown">
                        <?php if ($canReport): ?>
                        <li>
                            <a href="javascript:" data-propagate="1" onclick="joms.api.streamReport('<?php echo $stream_id; ?>', '<?php echo $wall->id; ?>');">
                                <?php echo JText::_('COM_COMMUNITY_REPORT'); ?>
                            </a>
                        </li>
                        <?php endif ?>

                        <?php if ($canEdit): ?>
                        <li>
                            <a href="javascript:" class="joms-button--edit" onclick="joms.api.commentEdit('<?php echo $wall->id; ?>', this);">
                                <span><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></span>
                            </a>
                        </li>
                        <?php endif ?>

                        <?php if($canRemove): ?>
                        <li>
                            <a href="javascript:" class="joms-button--remove" onclick="joms.api.commentRemove('<?php echo $wall->id; ?>');">
                                <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE'); ?></span>
                            </a>
                        </li>
                        <?php endif ?>
                    </ul>
                </div>
                <?php endif; ?>         
            <?php else: ?>
                <?php if ($my->id): ?>
                    <?php if ($isLiked != COMMUNITY_LIKE): ?>
                    <a class="joms-button--liked" href="javascript:" onclick="joms.api.commentLike('<?php echo $wall->id; ?>');"
                            data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                            data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="#joms-icon-thumbs-up"></use>
                        </svg>
                        <span><?php echo JText::_('COM_COMMUNITY_LIKE') ?></span>
                    </a>
                    <?php elseif ($my->id): ?>
                        <a class="joms-button--liked liked" href="javascript:" onclick="joms.api.commentUnlike('<?php echo $wall->id; ?>');"
                                data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                                data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="#joms-icon-thumbs-down"></use>
                            </svg>
                            <span><?php echo JText::_('COM_COMMUNITY_UNLIKE') ?></span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($likeCount > 0): ?>
                    <a href="javascript:" class="liked" data-action="showlike" onclick="joms.api.commentShowLikes('<?php echo $wall->id; ?>');">
                        <i class="joms-icon-thumbs-up"></i><span><?php echo (!CFactory::getUser()->id) ? JText::sprintf( CStringHelper::isPlural($likeCount) ? 'COM_COMMUNITY_GUEST_LIKES' : 'COM_COMMUNITY_GUEST_LIKE' , $likeCount ) : $likeCount; ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($canEdit): ?>
                    <a href="javascript:" class="joms-button--edit" onclick="joms.api.commentEdit('<?php echo $wall->id; ?>', this);">
                        <span><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if($canRemove): ?>
                    <a href="javascript:" class="joms-button--remove" onclick="joms.api.commentRemove('<?php echo $wall->id; ?>');">
                        <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE'); ?></span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (CActivitiesHelper::hasTag($my->id, $originalComment)): ?>
                <a href="javascript:" class="joms-button--remove-tag"
                   onclick="joms.api.commentRemoveTag('<?php echo $wall->id; ?>');">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="#joms-icon-remove"></use>
                    </svg>
                    <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE_TAG'); ?></span>
                </a>
            <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    <div class="joms-comment__reply joms-js--comment-editor">
        <div class="joms-textarea__wrapper" style="display:block">
            <div class="joms-textarea joms-textarea__beautifier"></div>
            <textarea class="joms-textarea" name="comment" data-id="<?php echo $wall->id; ?>" data-edit="1"
               <?php

                    // We need to do this because photo upload stream comments saved with reference to album->id, not stream->id.
                    if ( $wall->type === 'albums' ) {
                        echo ' data-tag-func="album" data-tag-id="' . $wall->contentid . '"';
                    } else if ( $wall->type === 'videos' ) {
                       echo 'data-tag-func="video" data-tag-id="' . $wall->contentid . '"';
                    }

                ?>
                placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>"><?php echo $originalComment; ?></textarea>
            <div class="joms-textarea__loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader" ></div>
            <div class="joms-textarea joms-textarea__attachment"<?php echo $photoThumbnail ? ' style="display:block"' : ' data-no_thumb="1"' ?>>
                <button onclick="joms.view.comment.removeAttachment(this);"<?php echo $photoThumbnail ? ' style="display:block"' : '' ?>>×</button>
                <div class="joms-textarea__attachment--loading"><img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader"></div>
                <div class="joms-textarea__attachment--thumbnail"<?php echo $photoThumbnail ? ' style="display:block"' : '' ?>>
                    <img<?php echo $photoThumbnail ? (' src="' . $photoThumbnail . '" data-photo_id="0"') : '' ?> alt="Attachment">
                </div>
            </div>
        </div>

        <div class="joms-icon joms-icon--emoticon" >
            <div style="position:relative">
                <svg viewBox="0 0 16 16" onclick="joms.view.comment.showEmoticonBoard(this);">
                    <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-smiley"></use>
                </svg>
            </div>
        </div>
        
        <svg viewBox="0 0 16 16" class="joms-icon joms-icon--add" onclick="joms.view.comment.addAttachment(this);" style="<?php echo (JFactory::getLanguage()->isRTL()) ? 'left' : 'right'; ?>:24px">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-camera"></use>
        </svg>
        <div style="text-align:right;margin-top:4px">
            <button class="joms-button--small joms-button--neutral" onclick="joms.view.comment.cancel('<?php echo $wall->id ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>
            <button class="joms-button--small joms-button--primary joms-js--btn-send"><?php echo JText::_('COM_COMMUNITY_SEND'); ?></button>
        </div>
    </div>
</div>
