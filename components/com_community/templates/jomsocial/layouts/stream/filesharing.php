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

/**
 * @since 3.2 we'll use CActivity for each activity object
 * @todo in sprint 3 we must move everything into CActivity while passing into template layout
 */
/* Temporary fix for sprint 2 */
if ($this->act instanceof CTableActivity) {
    /* If this's CTableActivity then we use getProperties() */
    $activity = new CActivity($this->act->getProperties());
} else {
    /* If it's standard object than we just passing it */
    $activity = new CActivity($this->act);
}

$stream = new stdClass();

$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;
$isSingleAct= ($jinput->get->get('actid',0) > 0) ? true : false;

$address = $activity->getLocation();
$user = $activity->getActor();
$target = $activity->getTarget();

if($act->pageid){
    $page  = JTable::getInstance( 'Page' , 'CTable' );
    $page->load( $act->pageid );
    $stream->page = $page;
    $act->appTitle = $page->name;
}

if($act->groupid){
    $group  = JTable::getInstance( 'Group' , 'CTable' );
    $group->load( $act->groupid );
    $stream->group = $group;
    $act->appTitle = $group->name;
}

if($act->eventid){
    $event  = JTable::getInstance( 'Event' , 'CTable' );
    $event->load( $act->eventid );
    $stream->event = $event;
    $act->appTitle = $event->title;
}

$stream->groupid = $act->groupid;

if (!empty($act->params)) {
    if (!is_object($act->params)) {
        $act->params = new JRegistry($act->params);
    }
    $mood = $act->params->get('mood', null);
} else {
    $mood = null;
}
$title = $activity->get('title');

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <?php if($user->id > 0) :?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
            </a>
        <?php endif; ?>
    </div>
    <div class="joms-stream__meta">

        <?php if($user->id > 0) :?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>" data-joms-username class="joms-stream__user active"><?php echo $user->getDisplayName(false, true); ?></a>
        <?php else :
            echo $user->getDisplayName(false, true);
        endif;

        if ($activity->get('eventid')) {
            ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::eventLink($event->id); ?>"><?php echo $event->title; ?></a>
            </span>
        <?php
        } else if ($activity->get('groupid')) {
            ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::groupLink($group->id); ?>"><?php echo $group->name; ?></a>
            </span>
        <?php
        } else if ($activity->get('pageid')) {
            ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::pageLink($page->id); ?>"><?php echo $page->name; ?></a>
            </span>
            <!-- Target is user profile -->
        <?php } else if ( ( $activity->get('app') == 'filesharing' ) && ( $activity->get('target') != 0 ) && $activity->get('target') != $user->id ) { ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::userLink($activity->target); ?>"><?php echo CFactory::getUser($activity->get('target'))->getDisplayName(); ?></a>
            </span>
        <?php } ?>

        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$activity->actor.'&actid='.$activity->id); ?>" style="display: inherit;">
            <span class="joms-stream__time">
                <small><?php echo $activity->getCreateTimeFormatted(); ?></small>
                <?php if ( strpos($activity->get('app'), 'events') === false  && strpos($activity->get('app'), 'groups') === false ) { ?>
                    <?php ( $activity->get('groupid') || ($activity->get('app') == 'filesharing') && $activity->get('target') != $activity->get('actor') ) ? '' : $this->load('/privacy/show'); ?>
                <?php } ?>
            </span>
        </a>
    </div>

    <?php

    $my = CFactory::getUser();
    $this->load('activities.stream.options');

    ?>

</div>
<div class="joms-stream__body">

    <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <textarea class="joms-textarea" style="margin-bottom:0"><?php echo $activity->get('title'); ?></textarea>
        <div style="text-align:right;">
            <button class="joms-button--neutral joms-button--small" onclick="joms.view.stream.cancel('<?php echo $activity->get('id'); ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>&nbsp;
            <button class="joms-button--primary joms-button--small" onclick="joms.view.stream.save('<?php echo $activity->get('id'); ?>', this);"><?php echo JText::_('COM_COMMUNITY_SAVE'); ?></button>
        </div>
    </div>

    <p data-type="stream-content">
        <?php $title =  empty($title) ? ltrim(CActivities::format($activity->get('title'), $mood),' -') : CActivities::format($activity->get('title'), $mood);

        echo CActivities::shorten($title, $activity->get('id'), $isSingleAct, $config->getInt('streamcontentlength'));
        ?>
    </p>

    <div class="joms-attachment-list">
        <ul>
            <?php
                $modelFile = CFactory::getModel('files');
                $files = $modelFile->getFileList('act', $activity->get('id'), 0, 100);

                foreach ($files as $file) {
                    $filename = $file->name;
                    $filepath = $file->filepath;
                    $fileext  = strrpos($filepath, '.');
                    $fileDownloadLink = CRoute::_('index.php?option=com_community&view=files&task=downloadfile&type=activity&id='.$file->id);

                    if ( $fileext !== false ) {
                        $filename .= substr($filepath, $fileext);
                    }

                    $fileLib = new CFilesLibrary();
                    $fileLib->convertToMb($file)
            ?>
                    <li>
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo JUri::getInstance() ?>#joms-icon-file-zip"></use>
                        </svg>
                        <a class="joms-filename" href="<?php echo $fileDownloadLink; ?>" target="_blank" title="<?php echo $filename; ?>" onclick="joms.api.fileUpdateHit(<?php echo $file->id; ?>);">
                            <?php echo $filename; ?>
                        </a>
                        <span class="joms-filesize"><?php echo $file->filesize; ?></span>
                    </li>
            <?php } ?>
        </ul>
    </div>
</div>

<?php $this->load('stream/footer'); ?>
