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

if (!$config->get('enablewalllikereaction')) {
    return '';
}
?>
<?php if (!$enablereaction): ?>
    <?php if ($allowComment || $allowLike || $showLike) { ?>
    <div class="joms-stream__status--mobile">

    <?php if ($allowLike || $showLike) { ?>
    <?php $displayLike = ($act->likeCount > 0 && $showLike) ? '':'display:none'; ?>

        <a class="joms-like__status" style="<?php echo $displayLike ?>" href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>', 'popup');">
            <span class="joms-like__counter--<?php echo $act->id; ?>"><?php echo $act->likeCount; ?></span>
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-thumbs-up"></use>
            </svg>
        </a>

    <?php } ?>

    <?php //if ($allowComment) { ?>
    <?php $displayComment = ($act->commentCount > 0) ? '':'display:none';  ?>

        <a class="joms-comment__status" style="<?php echo $displayComment ?>" href="javascript:" onclick="joms.api.streamShowComments('<?php echo $act->id; ?>');">
            <span class="joms-comment__counter--<?php echo $act->id; ?>"><?php echo $act->commentCount; ?></span>
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-bubble"></use>
            </svg>
        </a>


    </div>
    <?php } ?>
<?php endif ?>

<div class="joms-stream__actions">
    <?php if ($allowLike): ?>
        <?php if ($enablereaction): ?>
            <?php if($my->id): ?>
            <?php 
            $element = '';
            $reacted_id = $act->reacted_id;
            if ($act->app === 'photos') {
                $element = 'photo' . $act->params->get('photoid');
            }
            
            if ($act->app === 'videos' || $act->app === 'videos.linking') {
                $like = JTable::getInstance('Like','CTable');
                $like->loadInfo('videos', $act->cid);

                if ($like->id) {
                    $reactedUsers = explode(',', $like->like);
                    $reactions = explode(',', $like->reaction_ids);
                    
                    $key = array_search($my->id, $reactedUsers);
                    if ($key !== false) {
                        $reacted_id = $reactions[$key];
                    }
                }
            }

            echo CLikesHelper::renderReactionButton('stream', $element, $act->id, $reacted_id);
            ?>
            <?php endif; ?>
        <?php else: ?>
        <?php $userLiked = $act->userLiked == COMMUNITY_LIKE; ?>
        <a href="javascript:"
            class="joms-button--liked<?php echo $userLiked ? ' liked' : '' ?>"
            data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
            data-lang-unlike="<?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>"
            data-type="stream"
            data-stream-id="<?php echo $act->id ?>"
            onclick="joms.api.stream<?php echo $userLiked ? 'Unlike' : 'Like' ?>('<?php echo $act->id; ?>');">
            <span><?php echo JText::_($userLiked ? 'COM_COMMUNITY_UNLIKE' : 'COM_COMMUNITY_LIKE'); ?></span>
        </a>
        <?php endif ?>
    <?php endif; ?>
    <!-- share -->
    <?php
    //the only thing that we are able to share
    $allowShare = array(
        'pages.wall',
        'groups.wall', //group status - plain text, fetched content, with text and mood, location, fetched content
        'profile',
        'events.wall',
        'profile.avatar.upload', //profile avatar update
        'photos',
        'videos.linking', //linked videos
        'videos', //uploaded videos
        'groups', //group creation
        'groups.avatar.upload',
        'pages', //page creation
        'pages.avatar.upload',
        'events',
        'events.avatar.upload',
        'filesharing',
        'polls',
        'profile.status.share'
    );

    if ($my->id > 0 && $my->authorise('community.postcommentcreate', 'com_community') &&
        ( ($act->access == 0 || $act->access == 10) && ($act->group_access == 0 && $act->event_access == 0))
        && in_array($act->app, $allowShare) && $config->get('enablesharethis')
        //anything below this is no longer used, just for reference
        /*
        && $act->app != 'groups.bulletin'
        && $act->app != 'cover.upload'
        && strpos($act->app,'comment') === false
        && strpos($act->app,'featured') === false
        && $act->app != 'groups.discussion.reply'*/
    ) {

        // Re-share shared stream will share the original stream.
        $shareId = $act->id;
        if ( $act->app == 'profile.status.share' ) {
            $shareId = $act->params->get('activityId');
        }

        if ($act->groupid) {
            $shareURL = CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$act->groupid.'&actid='.$act->id);
        } else if ($act->pageid) {
            $shareURL = CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid='.$act->pageid.'&actid='.$act->id);
        } else if ($act->eventid) {
            $shareURL = CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$act->eventid.'&actid='.$act->id);
        } else {
            $shareURL = CRoute::getExternalURL('index.php?option=com_community&view=profile&userid='.$act->actor.'&actid='.$act->id);
        }
    ?>

        <a class="joms-button--share" href="javascript:" onclick="joms.api.pageShare('<?php echo $shareURL; ?>');">
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-redo"></use>
            </svg>
            <span><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></span>
        </a>
    <?php } ?>
    
    <?php 
    $allowShareTimeLine = array(
        'pages.wall',
        'groups.wall', //group status - plain text, fetched content, with text and mood, location, fetched content
        'profile',
        'events.wall',
        'profile.avatar.upload', //profile avatar update
        'photos',
        'videos.linking', //linked videos
        'videos', //uploaded videos
        'pages', //group creation
        'pages.avatar.upload',
        'groups', //group creation
        'groups.avatar.upload',
        'events',
        'events.avatar.upload'
    );

    if ($my->id > 0 && $my->authorise('community.postcommentcreate', 'com_community') && $my->id != $act->actor &&
        ( ($act->access == 0 || $act->access == 10) && ($act->group_access == 0 && $act->event_access == 0))
        && in_array($act->app, $allowShareTimeLine)) { ?>
        <?php 
            if (!isset($shareId)) {
                $shareId = $act->id;
            }
        ?>
        <a class="joms-button--share" href="javascript:" onclick="joms.api.streamShare('<?php echo $shareId; ?>');">
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-redo"></use>
            </svg>
            <span><?php echo JText::_('COM_COMMUNITY_SHARE_TIMELINE'); ?></span>
        </a>

    <?php } ?>
</div>

<?php if ($allowLike || $showLike): ?>
    <?php if ($enablereaction): ?>
        <?php 
            $status = CLikesHelper::streamRenderReactionStatus($act->id);
            $display = $status ? '' : 'style="display:none;"';
        ?>
        <div class="joms-stream__status" <?php echo $display ?>>
            <?php echo $status ?>
        </div>
    <?php else: ?>
        
        <?php if ($act->likeCount > 0 && $showLike): ?>
            <div class="joms-stream__status">
                <a href="javascript:" onclick="joms.api.streamShowLikes('<?php echo $act->id; ?>');"><?php
                    echo ($act->likeCount > 1)
                        ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY', $act->likeCount)
                        : CLikesHelper::streamShowLikes($act->id);
                ?></a>
            </div>
        <?php endif ?>
    <?php endif ?>
<?php endif ?>
