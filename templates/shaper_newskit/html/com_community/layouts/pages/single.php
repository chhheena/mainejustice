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
$config = CFactory::getConfig();
$aclaccess = CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pagedelete', 'com_community');
$aclpost = CFactory::getUser()->authorise('community.postcommentdelete', 'com_community') || CFactory::getUser()->authorise('community.postcommentedit', 'com_community');
$showPageDetails = ($page->approvals == 0 || ( $page->approvals == 1 && $isMember ) || $isSuperAdmin); //who can see the page details
$allowDiscussion = $config->get('creatediscussion');
$allowAnnouncement = $config->get('createannouncement');

$titleLength = $config->get('header_title_length', 60);
$summaryLength = $config->get('header_summary_length', 100);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$addFeaturedButton = false;
$isFeatured = false;
if ($isSuperAdmin && $config->get('show_featured') && !$page->unlisted) {
    $featured = new CFeatured(FEATURED_PAGES);
    $featuredList = $featured->getItemIds();

    $addFeaturedButton = true;
    
    if ( in_array($page->id, $featuredList) ) {
        $isFeatured = true;
    }
}
?>

<div class="joms-body">

<!-- focus area -->
<div class="joms-focus">
<div class="joms-focus__cover">
    <?php  if (in_array($page->id, $featuredList)) { ?>
    <div class="joms-ribbon__wrapper">
        <span class="joms-ribbon joms-ribbon--full"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
    </div>
    <?php } ?>

    <div class="joms-focus__cover-image joms-js--cover-image">
        <img src="<?php echo $page->getCover(); ?>" alt="<?php echo $page->name; ?>"
        <?php if (!$page->defaultCover && $page->coverAlbum) { ?>
            style="width:100%;top:<?php echo $page->coverPostion; ?>;cursor:pointer"
            onclick="joms.api.coverClick(<?php echo $page->coverAlbum ?>, <?php echo $page->coverPhoto ?>);"
        <?php } else { ?>
            style="width:100%;top:<?php echo $page->coverPostion; ?>"
        <?php } ?>>
    </div>

    <div class="joms-focus__cover-image--mobile joms-js--cover-image-mobile"
        <?php if (!$page->defaultCover && $page->coverAlbum) { ?>
            style="background:url(<?php echo $page->getCover(); ?>) no-repeat center center;cursor:pointer"
            onclick="joms.api.coverClick(<?php echo $page->coverAlbum ?>, <?php echo $page->coverPhoto ?>);"
        <?php } else { ?>
            style="background:url(<?php echo $page->getCover(); ?>) no-repeat center center"
        <?php } ?>>
    </div>

    <div class="joms-focus__header">
        <div class="joms-avatar--focus">
            <a <?php if ( !$page->defaultAvatar && $page->avatarAlbum ) { ?>
                href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $page->avatarAlbum); ?>" style="cursor:default"
                onclick="joms.api.photoOpen(<?php echo $page->avatarAlbum ?>); return false;"
            <?php } ?>>
                <img src="<?php echo $page->getAvatar('avatar') . '?_=' . time(); ?>" alt="<?php echo $this->escape($page->name); ?>">
                <?php if ($isAdmin || CFactory::getUser()->authorise('community.pageedit', 'com_community') || $isMine) { ?>
                <svg class="joms-icon" viewBox="0 0 16 16" onclick="joms.api.avatarChange('page', '<?php echo $page->id ?>', arguments && arguments[0]);">
                    <use xlink:href="#joms-icon-camera"></use>
                </svg>
                <?php } ?>
            </a>
        </div>
        <div class="joms-focus__title">
            <h2><?php echo CActivities::truncateComplex($page->name, $titleLength, true); ?></h2>
            <?php echo CStringHelper::ratingStar($ratingValue, 0, $page->id); ?>
            <div class="joms-focus__header__actions">
                <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $page->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $page->hits ); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $page->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                <?php } ?>

                <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_PAGE'); ?>"
                       href="javascript:" onclick="joms.api.pageReport('<?php echo $page->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                <?php } ?>

            </div>
            <p class="joms-focus__info--desktop">
                <?php echo CActivities::truncateComplex(strip_tags($page->summary), $summaryLength, true); ?>
            </p>
        </div>
        <div class="joms-focus__actions__wrapper">
            <div class="joms-focus__actions--desktop" style="position:relative">
                <?php if ($isMember) { ?>
                    <!-- invite friend button -->
                    <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.pageInvite('<?php echo $page->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="joms-focus__header__actions--desktop">

                <a class="joms-button--viewed nolink" title="<?php echo JText::sprintf( $page->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT', $page->hits ); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $page->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:" onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>')">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-redo"></use>
                        </svg>
                    </a>
                <?php } ?>

                <?php if ($enableReporting) { ?>
                    <a class="joms-button--viewed" title="<?php echo JText::_('COM_COMMUNITY_REPORT_PAGE'); ?>"
                       href="javascript:" onclick="joms.api.pageReport('<?php echo $page->id; ?>');">
                        <svg viewBox="0 0 16 16" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-warning"></use>
                        </svg>
                    </a>
                <?php } ?>

            </div>
        </div>
    </div>
    <div class="joms-focus__actions--reposition">
        <input type="button" class="joms-button--neutral" data-ui-object="button-cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL'); ?>"> &nbsp;
        <input type="button" class="joms-button--primary" data-ui-object="button-save" value="<?php echo JText::_('COM_COMMUNITY_SAVE'); ?>">
    </div>
    <?php if ($isMember && !$isBanned || $isSuperAdmin || (CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || CFactory::getUser()->authorise('community.pagedelete', 'com_community'))) { ?>
        <div class="joms-focus__button--options--desktop">
            <a href="javascript:" data-ui-object="joms-dropdown-button">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                </svg>
            </a>
            <!-- No need to populate menus as it is cloned from mobile version. -->
            <ul class="joms-dropdown"></ul>
        </div>
    <?php } ?>
</div>
<div class="joms-focus__actions" style="position:relative;">


    <?php // Invite
    if ($isMember) { ?>
        <a href="javascript:" class="joms-focus__button--add" onclick="joms.api.pageInvite('<?php echo $page->id; ?>')">
            <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
        </a>
    <?php } ?>


    <?php // Awaiting approval
    if ($waitingApproval) { ?>
        <a href="javascript:" class="joms-focus__button--message">
            <?php echo JText::_('COM_COMMUNITY_FRIENDS_AWAITING_AUTHORIZATION'); ?>
        </a>
        <div style="position:absolute;top:0;left:0;right:0;bottom:0"></div>
    <?php } ?>

    <?php if (
        ($isMember && !$isBanned && !$waitingApproval) ||
        $isSuperAdmin ||
        (CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || CFactory::getUser()->authorise('community.pagedelete', 'com_community'))
    ) { ?>
        <a class="joms-focus__button--options" data-ui-object="joms-dropdown-button"><?php echo JText::_('COM_COMMUNITY_PAGE_OPTIONS'); ?></a>
    <?php } ?>

    <ul class="joms-dropdown">

        <?php // @TODO: CAccess - Disable all options for non-members and non-superadmins
        if ( ($isMember && !$isBanned && !$waitingApproval) || $isSuperAdmin || (CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || $my->authorise('community.delete', 'pages.' . $page->id, $page))) { ?>

            <?php // @TODO: CAccess - Group admin actions
            if ($isAdmin || $isSuperAdmin || $isMine || CFactory::getUser()->authorise('community.pageedit', 'com_community')) { ?>
                <li><a href="javascript:" onclick="joms.api.avatarChange('page', '<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR'); ?></a></li>
                <li class="joms-js--menu-reposition joms-hidden--small"<?php echo $page->defaultCover ? ' style="display:none"' : '' ?>>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.coverReposition('page', <?php echo $page->id; ?>);"><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER'); ?></a>
                </li>
                <li><a href="javascript:" onclick="joms.api.coverChange('page', <?php echo $page->id; ?>);"><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a></li>
                <li class="joms-js--menu-remove-cover"<?php echo $page->defaultCover ? ' style="display:none"' : '' ?>>
                    <a href="javascript:" data-propagate="1" onclick="joms.api.coverRemove('page', <?php echo $page->id; ?>);"><?php echo JText::_('COM_COMMUNITY_REMOVE_COVER'); ?></a>
                </li>

                <?php if ($addFeaturedButton) { ?>
                    <?php if ($isFeatured) { ?>
                        <li><a href="javascript:" onclick="joms.api.pageRemoveFeatured('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a></li>
                    <?php } else { ?>
                        <li><a href="javascript:" onclick="joms.api.pageAddFeatured('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PAGE_FEATURE'); ?></a></li>
                    <?php } ?>
                <?php } ?>

                <li class="divider"></li>
            <?php } ?>

            <?php if ($isMember || $isMine || $isAdmin || $isSuperAdmin) { ?>
            <li>
                <a href="javascript:" onclick="joms.api.pageInvite('<?php echo $page->id ?>');"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></a>
            </li>
            <li class="divider"></li>
            <?php } ?>

            <?php // @TODO CAccess - More admin actions
            if ($isMine || $isSuperAdmin || $isAdmin || (CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || $my->authorise('community.delete', 'pages.' . $page->id, $page))) { ?>

                <?php if ($isMine || $isAdmin || CFactory::getUser()->authorise('community.pageedit', 'com_community')) { ?>
                <li>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=edit&pageid=' . $page->id); ?>"><?php echo JText::_('COM_COMMUNITY_PAGES_EDIT'); ?></a>
                </li>
                <?php } ?>

                <?php // ACL check
                if (CFactory::getUser()->authorise('community.pageeditstate', 'com_community')) { ?>
                    <li>
                        <a href="javascript:" onclick="joms.api.pageUnpublish('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PAGES_UNPUBLISH'); ?></a>
                    </li>
                <?php } ?>
                <?php if($my->authorise('community.delete', 'pages.' . $page->id, $page) ) { ?>
                <li class="divider"></li>
                <li>
                    <a href="javascript:" onclick="joms.api.pageDelete('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PAGES_DELETE_PAGE_BUTTON'); ?></a>
                </li>
                    <?php } ?>
            <?php } ?>

        <?php } ?>
    </ul>
</div>
<?php
//do not show this to non-members if this is a private page
if($showPageDetails || $aclaccess){
    ?>
    <ul class="joms-focus__link">
        <?php if ($showPhotos) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&pageid=' . $page->id); ?>"><?php echo ($totalPhotos == 1) ?
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                        JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' ; ?></a>
            </li>
        <?php } ?>

        <?php if ($showVideos) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&pageid=' . $page->id); ?>">
                    <?php echo ($totalVideos == 1)
                        ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                        : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>' ; ?>
                </a>
            </li>
        <?php } ?>
        
        <?php if ($showEvents) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&pageid=' . $page->id); ?>"><?php echo ($totalEvents == 1)
                        ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                        : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>' ; ?></a>
            </li>
        <?php } ?>

        <?php if ($showPolls) { ?>
            <li class="half">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pageid=' . $page->id); ?>"><?php echo ($totalPolls == 1)
                        ? JText::_('COM_COMMUNITY_POLLS_COUNT') . ' <span class="joms-text--light">' . $totalPolls . '</span>'
                        : JText::_('COM_COMMUNITY_POLLS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalPolls . '</span>' ; ?></a>
            </li>
        <?php } ?>
        
        <li class="half">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid=' . $page->id); ?>"><?php echo ($membersCount == 1)
                    ? JText::_('COM_COMMUNITY_GROUPS_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                    : JText::_('COM_COMMUNITY_GROUPS_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?></a>
        </li>

        <li class="half">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id); ?>"><?php echo ($reviewsCount == 1)
                    ? JText::_('COM_COMMUNITY_PAGES_REVIEW') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'
                    : JText::_('COM_COMMUNITY_PAGES_REVIEWS') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'; ?></a>
        </li>

        <?php if ($isAdmin || $isSuperAdmin){ ?>
            <li class="full">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown more-button">
                    <?php if ($isAdmin || $isSuperAdmin) { ?>
                        <li>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=banlist&list=-1&pageid='.$page->id) ?>">
                                <?php echo JText::_('COM_COMMUNITY_PAGES_BANNED_MEMBERS') ; ?>
                                <span class="joms-text--light"><?php echo $totalBannedMembers; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>
        <?php } ?>
        
        <?php if(!$waitingApproval && !$isBanned) { ?>
            <li class="full liked">
                <a href="javascript:"
                   class="joms-js--like-pages-<?php echo $page->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
                   onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('pages', '<?php echo $page->id; ?>');"
                   data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                   data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                    </svg>
                    <span class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                    <span class="joms-text--light"> <?php echo $totalLikes ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>
<?php } ?>
</div>

<div class="joms-sidebar">
<div class="joms-module__wrapper"><?php $this->renderModules('js_side_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_top_stacked'); ?></div>
<div class="joms-module__wrapper"><?php $this->renderModules('js_pages_side_top'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_pages_side_top_stacked'); ?></div>

<?php
//do not show this to non-members if this is a private page
if($showPageDetails || $aclaccess){
    $isFirstTab = 1;
    ?>
    <div class="joms-module__wrapper">
        <div class="joms-tab__bar">
            <?php if($allowDiscussion) { ?>
                <a href="#joms-page--discussion" class="active no-padding">
                    <div class="joms-tab__bar--button">
                        <span class="title"><?php echo JText::_('COM_COMMUNITY_DISCUSSIONS'); ?></span>
                        <?php if($my->authorise('community.create', 'pages.discussions.' . $page->id)){ ?>
                        <span class="add" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=pages&task=adddiscussion&pageid='.$page->id); ?>'">
                            <svg class="joms-icon" viewBox="0 -5 15 30">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                            </svg>
                        </span>
                        <?php } ?>
                    </div>
                </a>
                <?php
                $isFirstTab = 0;
            }?>

            <?php if($allowAnnouncement) { ?>
                <a href="#joms-page--announcement" class="<?php if($isFirstTab) echo "active";?> no-padding">
                    <div class="joms-tab__bar--button">
                        <span class="title"><?php echo JText::_('COM_COMMUNITY_ANNOUNCEMENTS'); ?></span>
                        <?php if($my->authorise('community.create', 'pages.announcement.' . $page->id)){ ?>
                        <span class="add" onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=pages&task=addnews&pageid='.$page->id); ?>'">
                            <svg class="joms-icon" viewBox="0 -5 15 30">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                            </svg>
                        </span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>

<?php } ?>

<!-- Page's Module' -->
<?php if ($page->approvals == '0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin || $aclaccess) { ?>
    <div class="joms-module__wrapper">

        <div class="joms-tab__bar">
            <a href="#joms-page--members" class="active"><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_MEMBERS'); ?></a>
            <?php if($showEvents) { ?>
                <a href="#joms-page--events" ><?php echo JText::sprintf('COM_COMMUNITY_EVENTS'); ?></a>
            <?php } ?>
        </div>

        <!-- Page's Members @ Sidebar -->
        <?php if ($members) { ?>
            <div id="joms-page--members" class="joms-tab__content">

                <ul class="joms-list--photos clearfix">
                    <?php foreach ($members as $member) { ?>
                        <li class="joms-list__item">
                            <div class="joms-avatar <?php echo CUserHelper::onlineIndicator($member); ?>">
                                <a href="<?php echo CUrlHelper::userLink($member->id); ?>">
                                    <img
                                        src="<?php echo $member->getThumbAvatar(); ?>"
                                        title="<?php echo CTooltip::cAvatarTooltip($member); ?>"
                                        alt="<?php echo CTooltip::cAvatarTooltip($member); ?>" data-author="<?php echo $member->id; ?>" />
                                </a>
                            </div>
                        </li>
                        <?php if (--$limit < 1) {
                            break;
                        }
                    } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=pages&task=viewmembers&pageid=' . $page->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIEW_ALL'); ?> (<?php echo $membersCount; ?>)
                    </a>
                </div>
            </div>
        <?php } ?>
        <!-- Page's Members @ Sidebar -->

        <!-- Page Events @ Sidebar -->
        <?php if ($showEvents) { ?>
            <div id="joms-page--events" class="joms-tab__content" style="display:none;">

                <?php if ($events) { ?>
                    <ul class="joms-list--event">
                        <?php
                        foreach ($events as $event) {
                            $creator = CFactory::getUser($event->creator);
                            ?>
                            <li class="joms-media--event">
                                <div class="joms-media__calendar">
                                    <?php
                                    $datestr = strtotime($event->getStartDate());
                                    $day = date('d', $datestr);
                                    $month = date('M', $datestr);
                                    $year = date('y', $datestr);
                                    ?>
                                    <span class="month"><?php echo $month; ?></span>
                                    <span class="date"><?php echo $day; ?></span>
                                </div>

                                <div class="joms-media__body">
                                    <div class="event-detail">
                                        <a href="<?php echo CRoute::_(
                                            'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id . '&pageid=' . $page->id
                                        ); ?>" class="cThumb-Title">
                                            <?php echo $event->title; ?>
                                        </a>

                                        <div class="cThumb-Location">
                                            <?php // echo $event->getCategoryName();?>
                                            <?php echo $event->location; ?>
                                        </div>
                                        <!-- <div class="eventTime"><?php echo JText::sprintf(
                                            'COM_COMMUNITY_EVENTS_DURATION',
                                            JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2')),
                                            JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2'))
                                        ); ?></div> -->
                                        <div class="cThumb-Members">
                                            <a href="<?php echo CRoute::_(
                                                'index.php?option=com_community&view=events&task=viewguest&pageid=' . $page->id . '&eventid=' . $event->id . '&type=' . COMMUNITY_EVENT_STATUS_ATTEND
                                            ); ?>"><?php echo JText::sprintf(
                                                    (!CStringHelper::isSingular(
                                                        $event->confirmedcount
                                                    )) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT' : 'COM_COMMUNITY_EVENTS_GUEST_COUNT',
                                                    $event->confirmedcount
                                                ); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_EVENTS_NOT_CREATED'); ?></div>
                <?php } ?>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=events&pageid=' . $page->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_EVENTS'); ?>
                    </a>
                </div>
            </div>

        <?php } ?>
    </div>
    <!-- Page Events @ Sidebar -->
    <div class="joms-module__wrapper">
        <div class="joms-tab__bar">

            <?php if ($showPhotos) { ?>
            <?php if ($albums) { ?>
                <a href="#joms-page--photos" class="active"><?php echo JText::_('COM_COMMUNITY_PHOTOS_PHOTO_ALBUMS'); ?></a>
            <?php } ?>
            <?php } ?>

            <?php if ($showVideos) { ?>
            <?php if ($videos) { ?>
                <a href="#joms-page--videos"><?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?></a>
            <?php } ?>
            <?php } ?>

        </div>

        <?php if ($showPhotos) { ?>
        <?php if ($albums) { ?>
            <div id="joms-page--photos" class="joms-tab__content">
                <ul class="joms-list--photos">
                    <?php foreach ($albums as $album) { ?>
                        <li class="joms-list__item">
                            <a href="<?php echo CRoute::_(
                                'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&pageid=' . $page->id
                            ); ?>">
                                <img class="cAvatar cMediaAvatar jomNameTips"
                                     title="<?php echo $this->escape($album->name); ?>"
                                     src="<?php echo $album->getCoverThumbURI(); ?>"
                                     alt="<?php echo $album->getCoverThumbURI(); ?>"/>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=photos&pageid=' . $page->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIEW_ALL_ALBUMS') . ' (' . $totalAlbums . ')'; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
        <?php } ?>

        <?php if ($showVideos) { ?>
        <?php if ($videos) { ?>
            <div id="joms-page--videos" class="joms-tab__content" style="display:none;" >
                <ul class="joms-list--videos">
                    <?php foreach ($videos as $video) { ?>
                        <li class="joms-list__item">
                            <a href="<?php echo $video->getURL(); ?>"
                               title="<?php echo $video->title; ?>">
                                <img src="<?php echo $video->getThumbnail(); ?>" class="joms-list__cover" alt="<?php echo $video->title; ?>" />
                                <span class="joms-video__duration"><?php echo $video->getDurationInHMS(); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="cUpdatesHelper clearfull">
                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=videos&pageid=' . $page->id
                    ); ?>">
                        <?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL') . ' (' . $totalVideos . ')'; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
        <?php } ?>

    </div>

<?php } ?>

<div class="joms-module__wrapper"><?php $this->renderModules('js_pages_side_bottom'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_pages_side_bottom_stacked'); ?></div>
<div class="joms-module__wrapper"><?php $this->renderModules('js_side_bottom'); ?></div>
<div class="joms-module__wrapper--stacked"><?php $this->renderModules('js_side_bottom_stacked'); ?></div>

</div>

<div class="joms-main">

    <div class="joms-middlezone">

        <!-- Page's Approval -->
        <?php if (($isMine || $isAdmin || $isSuperAdmin) && ($unapproved > 0)) { ?>
            <div id="joms-page--approval" class="joms-alert--info">
                <svg viewBox="0 0 20 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                </svg>
                <a class="friend" href="<?php echo CRoute::_(
                    'index.php?option=com_community&view=pages&task=viewmembers&approve=1&pageid=' . $page->id
                ); ?>">
                    <?php echo JText::sprintf(
                        (CStringHelper::isPlural(
                            $unapproved
                        )) ? 'COM_COMMUNITY_PAGES_APPROVAL_NOTIFICATION_MANY' : 'COM_COMMUNITY_PAGES_APPROVAL_NOTIFICATION',
                        $unapproved
                    ); ?>
                </a>
            </div>
        <?php } ?>
        <!-- Page's Approval -->

        <!-- Waiting Approval -->
        <?php if ($waitingApproval) { ?>
            <div class="joms-alert--info">
                <svg viewBox="0 0 20 20" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-clock"></use>
                </svg>
                <span><?php echo JText::_('COM_COMMUNITY_PAGES_APPROVAL_PENDING'); ?></span>
            </div>
        <?php } ?>

        <?php if ($isInvited) { ?>
            <div id="pages-invite-<?php echo $page->id; ?>" class="joms-alert--info">
                <h4 class="joms-alert__head">
                    <?php echo JText::sprintf('COM_COMMUNITY_PAGES_INVITATION', $join); ?>
                </h4>

                <div class="joms-alert__body">
                    <div class="joms-alert__content">
                        <?php echo JText::sprintf('COM_COMMUNITY_PAGES_YOU_INVITED', $join); ?>
                        <span>
                                <?php echo JText::sprintf(
                                    (CStringHelper::isPlural(
                                        $friendsCount
                                    )) ? 'COM_COMMUNITY_PAGES_FRIEND' : 'COM_COMMUNITY_PAGES_FRIEND_MANY',
                                    $friendsCount
                                ); ?>
                            </span>
                    </div>
                    <div class="joms-alert__actions">
                        <a href="javascript:void(0);"
                           onclick="jax.call('community','pages,ajaxRejectInvitation','<?php echo $page->id; ?>');" class="joms-button--neutral joms-button--small">
                            <?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="jax.call('community','pages,ajaxAcceptInvitation','<?php echo $page->id; ?>');"
                           class="joms-button--primary joms-button--small">
                            <?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="joms-tab__bar">
            <?php
            //do not show this to non-members if this is a private page
            if($showPageDetails || $aclpost){
                ?>
                <a href="#joms-page--stream" class="<?php echo (!$showPageDetails || !$config->get('default_page_tab')) ? 'active' : ''; ?>">
                    <?php echo JText::_('COM_COMMUNITY_ACTIVITIES'); ?>
                </a>
            <?php } ?>
            <a href="#joms-page--details" class="<?php echo (($showPageDetails && $config->get('default_page_tab')) || (!$showPageDetails) && !$aclpost) ? 'active' : ''; ?>">
                <?php echo JText::_('COM_COMMUNITY_PAGE_DETAILS'); ?>
            </a>
        </div>

        <div class="joms-gap"></div>

        <div id="joms-page--stream" class="joms-tab__content" style="<?php echo (!$showPageDetails || !$config->get('default_page_tab')) ? '' : 'display:none'; ?>">
            <?php if($isMember || $isSuperAdmin || $isAdmin || (!$config->get('lockpagewalls', false) && !$page->approvals)) {$status->render();} ?>
            <?php echo $streamHTML; ?>
        </div>
        <div id="joms-page--details" class="joms-tab__content" style="<?php echo (($showPageDetails && $config->get('default_page_tab')) || (!$showPageDetails && !$aclpost)) ? '' : 'display:none'; ?>">
            <ul class="joms-list__row">
                <li>
                    <span>
                        <?php echo $page->description; ?>
                        <?php
                        //find out if there is any url here, if there is, run it via embedly when enabled
                        $params = new CParameter($page->params);
                        if ($params->get('url') && $config->get('enable_embedly')) {
                            if (!preg_match("@^[hf]tt?ps?://@", $params->get('url'))) {
                                $url = "http://" . $params->get('url');
                            } else {
                                $url = $params->get('url');
                            }
                            ?>
                            <a href="<?php echo $url; ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-key="<?php echo $config->get('embedly_card_apikey'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
                        <?php } ?>
                    </span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_PAGES_CATEGORY'); ?></h5>
                        <span><a href="<?php echo CRoute::_(
                                'index.php?option=com_community&view=pages&categoryid=' . $page->categoryid
                            ); ?>"><?php echo JText::_($page->getCategoryName()); ?></a></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_PAGES_CREATE_TIME'); ?></h5>
                    <span><?php echo JHTML::_('date', $page->created, JText::_('DATE_FORMAT_LC')); ?></span>
                </li>
                <li>
                    <h5 class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_PAGES_ADMINS'); ?></h5>
                    <span><?php echo $adminsList; ?></span>
                </li>
            </ul>
        </div>

    </div>

</div>

</div>

<script>
    // Clone menu from mobile version to desktop version.
    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            var src = joms.jQuery('.joms-focus__actions ul.joms-dropdown'),
                clone = joms.jQuery('.joms-focus__button--options--desktop ul.joms-dropdown');

            clone.html( src.html() );
        });
    })( window );
</script>

<script>

    // override config setting
    joms || (joms = {});
    joms.constants || (joms.constants = {});
    joms.constants.conf || (joms.constants.conf = {});

    joms.constants.pageid = <?php echo $page->id; ?>;
    joms.constants.videocreatortype = '<?php echo VIDEO_PAGE_TYPE ?>';
    joms.constants.conf.enablephotos = <?php echo (isset($showPhotos) && $showPhotos == 1 && (( ($isAdmin || COwnerHelper::isCommunityAdmin()) && $photoPermission == 1 ) || (($isMember || COwnerHelper::isCommunityAdmin()) && $photoPermission == 2) ) && CFactory::getUser()->authorise('community.photocreate', 'com_community')) ? 1 : 0 ; ?>;
    joms.constants.conf.enablevideos = <?php echo (isset($showVideos) && $showVideos == 1 && (( ($isAdmin || COwnerHelper::isCommunityAdmin()) && $videoPermission == 1 ) || (($isMember || COwnerHelper::isCommunityAdmin()) && $videoPermission == 2) ) && CFactory::getUser()->authorise('community.videocreate', 'com_community')) ? 1 : 0 ; ?>;
    joms.constants.conf.enablevideosupload  = <?php echo $config->get('enablevideosupload');?>;
    joms.constants.conf.enableevents = <?php echo (isset($showEvents) && $showEvents == 1 && (( ($isAdmin || COwnerHelper::isCommunityAdmin()) && $eventPermission == 1 ) || (($isMember || COwnerHelper::isCommunityAdmin()) && $eventPermission == 2) ) && $my->canCreateEvents() ) ? 1 : 0 ; ?>;

</script>
