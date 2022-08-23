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

$my = CFactory::getUser();

// Replace <br> to newline.
$wall->originalComment = preg_replace('/<br\s*\/>/i', "\n", $wall->originalComment);
$enablereaction = $config->get('enablereaction');
?>

<div class="joms-comment__item <?php echo "joms-embedly--" . $config->get('enable_embedly_card_position'); ?> joms-js--comment joms-js--comment-<?php echo $wall->id; ?>" data-id="<?php echo $wall->id; ?>" data-parent="<?php echo $wall->contentid; ?>">
    <div class="joms-comment__header">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator(CFactory::getUser($wall->post_by)); ?>">
            <?php echo $avatarHTML; ?>
        </div>
        <div class="joms-comment__body joms-js--comment-body">
            <a class="joms-comment__user" href="<?php echo $authorLink; ?>"><?php echo $author; ?></a>
            <span class="joms-js--comment-content"><?php

                echo CActivities::shorten($content, $wall->id, 0, $config->getInt('stream_comment_length'), 'comment');

                ?></span>
            <?php if (!empty($photoThumbnail)) { ?>
                <div style="padding: 5px 0">
                    <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $photoThumbnail; ?>');">
                        <img class="joms-stream-thumb" src="<?php echo $photoThumbnail; ?>" alt="photo thumbnail" />
                    </a>
                </div>
            <?php } else {
                if ($paramsHTML) { ?>
                    <?php echo $paramsHTML; ?>
                <?php }
            } ?>
            <span class="joms-comment__time"><small><?php echo $created; ?></small></span>

            <?php if(CFactory::getUser()->id){ ?>
            <div class="joms-comment__actions joms-js--comment-actions">
                <?php if ($enablereaction): ?>
                <?php
                $reactionData = CStringHelper::getReactionData();
                $reacted = array_filter( $reactionData, function($item) use ($reactedId) {
                    return $item->id == $reactedId;
                });

                $reacted = array_shift($reacted);
                $className = $reacted ? 'reaction-btn--' . $reacted->name : '';
                $onclick = $reacted ? 'joms.view.comment.unreact('.$wall->id.', '.$reacted->id.')'
                                    : 'joms.view.comment.react('.$wall->id.', 1)';
                ?>
                <a  class="joms-button--reaction <?php echo $className ?>" 
                    href="javascript:;"
                    data-type="comment"
                    data-uid="<?php echo $wall->id ?>"
                    data-lang-like="<?php echo JText::_('COM_COMMUNITY_REACTION_LIKE') ?>"
                    onclick="<?php echo $onclick ?>">
                    <?php echo $reacted ? $reacted->text : JText::_('COM_COMMUNITY_REACTION_LIKE') ?>
                </a>

                <div class="joms-comment__reaction-status">
                <?php echo CLikesHelper::commentRenderReactionStatus($wall->id); ?>
                </div>

                <?php if($isEditable || $canDelete): ?>
                <div class="joms-list__options">
                    <a href="javascript:" class="joms-button--options" data-ui-object="joms-dropdown-button" data-type="no-popup">
                        &#8226;&#8226;&#8226;
                    </a>
                    <ul class="joms-dropdown">
                        <?php if ($isEditable): ?>
                        <li>
                            <a href="javascript:" class="joms-button--edit" onclick="joms.api.commentEdit('<?php echo $wall->id; ?>', this);">
                                <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-pencil"></use></svg>
                                <span><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></span>
                            </a>
                        </li>
                        <?php endif ?>

                        <?php if($canDelete): ?>
                        <li>
                            <a href="javascript:" class="joms-button--remove" onclick="joms.api.commentRemove('<?php echo $wall->id; ?>');">
                                <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-remove"></use></svg>
                                <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE'); ?></span>
                            </a>
                        </li>
                        <?php endif ?>
                    </ul>
                </div>
                <?php endif; ?>     

                <?php else: ?>
                <a href="javascript:"
                    class="joms-button--liked<?php echo $isLiked ? ' liked' : '' ?>"
                    data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                    data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>"
                    onclick="joms.api.comment<?php echo $isLiked ? 'Unlike' : 'Like' ?>('<?php echo $id; ?>');">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-<?php echo $isLiked ? 'down' : 'up' ?>"></use>
                    </svg>
                    <span><?php echo JText::_($isLiked ? 'COM_COMMUNITY_UNLIKE' : 'COM_COMMUNITY_LIKE'); ?></span>
                </a><?php

                    if ($likeCount > 0) {

                ?><a href="javascript:" data-action="showlike" onclick="joms.api.commentShowLikes('<?php echo $wall->id; ?>');">
                        <i class="joms-icon-thumbs-up"></i><span><?php echo $likeCount; ?></span>
                    </a>
                <?php } ?>

                <?php if ($isEditable) { ?>
                <a href="javascript:" class="joms-button--edit" onclick="joms.api.commentEdit('<?php echo $id; ?>', this, 'wall');">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-pencil"></use>
                    </svg>
                    <span><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></span>
                </a>
                <?php } ?>

                <?php if($canDelete){?>
                <a href="javascript:" class="joms-button--remove" onclick="joms.api.commentRemove('<?php echo $id; ?>', 'wall');">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-remove"></use>
                    </svg>
                    <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE'); ?></span>
                </a>
                <?php } ?>

                <?php if ( CActivitiesHelper::hasTag($my->id, $wall->originalComment) ) { ?>
                    <a href="javascript:" class="joms-button--remove-tag" onclick="joms.api.commentRemoveTag('<?php echo $id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-remove"></use></svg>
                        <span><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE_TAG'); ?></span>
                    </a>
                <?php } ?>
                <?php endif ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="joms-comment__reply joms-js--comment-editor">
        <div class="joms-textarea__wrapper" style="display:block">
            <div class="joms-textarea joms-textarea__beautifier"></div>
            <textarea class="joms-textarea" name="comment"
                data-id="<?php echo $id; ?>"
                data-func="<?php echo $processFunc ?>"
                data-edit="1"
                placeholder="<?php echo JText::_('COM_COMMUNITY_WRITE_A_COMMENT'); ?>"><?php echo $wall->originalComment; ?></textarea>
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
