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

$featured = new CFeatured(FEATURED_PAGES);
$featuredList = $featured->getItemIds();

$titleLength= $config->get('header_title_length', 30);
$summaryLength = $config->get('header_summary_length', 80);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

if ($page->approvals == COMMUNITY_PRIVATE_PAGE && !$page->isMember($my->id) && !CFactory::getUser()->authorise('community.pageedit', 'com_community') && !CFactory::getUser()->authorise('community.pageeditstate', 'com_community') && !CFactory::getUser()->authorise('community.pagedelete', 'com_community')) {
    return false;
}
?>

<div class="joms-body">
<div class="joms-focus">
<div class="joms-focus__cover joms-focus--mini">
    <?php  if (in_array($page->id, $featuredList)) { ?>
    <div class="joms-ribbon__wrapper">
        <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
    </div>
    <?php } ?>

    <div class="joms-focus__cover-image--mobile" style="background:url(<?php echo $page->getCover(); ?>) no-repeat center center;">
    </div>

    <div class="joms-focus__header">
        <div class="joms-avatar--focus">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>">
                <img src="<?php echo $page->getAvatar('avatar') . '?_=' . time(); ?>"
                     alt="<?php echo $page->name; ?>"/>
            </a>
        </div>

        <div class="joms-focus__title">
            <h3>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>">
                    <?php echo CActivities::truncateComplex($this->escape($page->name), $titleLength, true); ?>
                </a>
            </h3>
            <?php echo CStringHelper::ratingStar($ratingValue, 0, $page->id); ?>

            <div class="joms-focus__header__actions">
                <a class="joms-button--viewed nolink"
                   title="<?php echo JText::sprintf($page->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT',
                       $page->hits); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $page->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:"
                       onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>')">
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
                <?php echo CActivities::truncateComplex($this->escape(strip_tags($page->summary)), $summaryLength); ?>
            </p>
        </div>

        <div class="joms-focus__actions__wrapper">
            <div class="joms-focus__actions--desktop">
                <?php if ($isMember) { ?>
                    <!-- invite friend button -->
                    <a href="javascript:" class="joms-focus__button--add"
                       onclick="joms.api.pageInvite('<?php echo $page->id; ?>')">
                        <?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="joms-focus__header__actions--desktop">

                <a class="joms-button--viewed nolink"
                   title="<?php echo JText::sprintf($page->hits > 0 ? 'COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY' : 'COM_COMMUNITY_VIDEOS_HITS_COUNT',
                       $page->hits); ?>">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"></use>
                    </svg>
                    <span><?php echo $page->hits; ?></span>
                </a>

                <?php if ($config->get('enablesharethis') == 1) { ?>
                    <a class="joms-button--shared" title="<?php echo JText::_('COM_COMMUNITY_SHARE_THIS'); ?>"
                       href="javascript:"
                       onclick="joms.api.pageShare('<?php echo CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id); ?>')">
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
</div>

<?php
    //do not show this to non-members if this is a private page
    if ($page->approvals == 0 || ($page->approvals == 1 && $isMember) || (CFactory::getUser()->authorise('community.pageeditstate', 'com_community') || CFactory::getUser()->authorise('community.pageedit', 'com_community') || CFactory::getUser()->authorise('community.pagedelete', 'com_community'))) {
        ?>
        <ul class="joms-focus__link">
            <?php if ($showPhotos) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&pageid=' . $page->id); ?>"><?php echo ($totalPhotos == 1) ?
                            JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                            JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' ; ?></a>
                </li>
            <?php } ?>

            <?php if ($showVideos) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&pageid=' . $page->id); ?>">
                        <?php echo ($totalVideos == 1)
                            ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                            : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>' ; ?>
                    </a>
                </li>
            <?php } ?>

            <?php if ($showEvents) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&pageid=' . $page->id); ?>"><?php echo ($totalEvents == 1)
                            ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                            : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>' ; ?></a>
                </li>
            <?php } ?>

            <?php if ($showPolls) { ?>
                <li class="half hidden-mobile">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pageid=' . $page->id); ?>"><?php echo ($totalPolls == 1)
                            ? JText::_('COM_COMMUNITY_POLLS_COUNT') . ' <span class="joms-text--light">' . $totalPolls . '</span>'
                            : JText::_('COM_COMMUNITY_POLLS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalPolls . '</span>' ; ?></a>
                </li>
            <?php } ?>

            <li class="half hidden-mobile">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid=' . $page->id); ?>">
                    <?php echo ($membersCount == 1)
                        ? JText::_('COM_COMMUNITY_PAGES_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                        : JText::_('COM_COMMUNITY_PAGES_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?>
                </a>
            </li>

            <li class="half hidden-mobile">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id); ?>">
                    <?php echo ($reviewsCount == 1)
                        ? JText::_('COM_COMMUNITY_PAGES_REVIEW') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'
                        : JText::_('COM_COMMUNITY_PAGES_REVIEWS') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'; ?>
                </a>
            </li>

            <?php if (
                    ((($isAdmin) || ($isMine) || ($isMember && !$isBanned)) && $isFile) ||
                    ($isAdmin || $isSuperAdmin)
                ) { ?>
            <li class="half hidden-mobile">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                
                <ul class="joms-dropdown more-button">                                        
                    <?php if ((($isAdmin) || ($isMine) || ($isMember && !$isBanned)) && $isFile) { ?>
                        <li>
                            <a href="javascript:" onclick="joms.api.fileList('page',<?php echo $page->id ?>)">
                                <?php echo ($isFile == 1)
                                    ? JText::_('COM_COMMUNITY_PAGES_FILE_COUNT') . ' <span class="joms-text--light">' . $isFile . '</span>'
                                    : JText::_('COM_COMMUNITY_PAGES_FILE_COUNT_MANY') . ' <span class="joms-text--light">' . $isFile . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>
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
            
            <li class="half hidden-desktop">
                <a href="javascript:" data-ui-object="joms-dropdown-button">
                    <?php echo JTEXT::_('COM_COMMUNITY_MORE'); ?>
                    <svg viewBox="0 0 14 20" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown more-button">
                    <?php if ($showPhotos) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&pageid=' . $page->id); ?>">
                                <?php echo ($totalPhotos == 1) ?
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') . ' <span class="joms-text--light">' . $totalPhotos . '</span>' :
                                    JText::_('COM_COMMUNITY_PHOTOS_COUNT') . ' <span class="joms-text--light">' . $totalPhotos . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($showVideos) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&pageid=display&pageid=' . $page->id); ?>">
                                <?php echo ($totalVideos == 1)
                                    ? JText::_('COM_COMMUNITY_VIDEOS_COUNT') . ' <span class="joms-text--light">' . $totalVideos . '</span>'
                                    : JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalVideos . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($showEvents) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&pageid=' . $page->id); ?>">
                                <?php echo ($totalEvents == 1)
                                    ? JText::_('COM_COMMUNITY_EVENTS_COUNT') . ' <span class="joms-text--light">' . $totalEvents . '</span>'
                                    : JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalEvents . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>
                    
                    <?php if ($showPolls) { ?>
                        <li class="half">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pageid=' . $page->id); ?>"><?php echo ($totalPolls == 1)
                                    ? JText::_('COM_COMMUNITY_POLLS_COUNT') . ' <span class="joms-text--light">' . $totalPolls . '</span>'
                                    : JText::_('COM_COMMUNITY_POLLS_COUNT_MANY') . ' <span class="joms-text--light">' . $totalPolls . '</span>' ; ?></a>
                        </li>
                    <?php } ?>

                    <li class="hidden-desktop">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid=' . $page->id); ?>">
                            <?php echo ($membersCount == 1)
                                ? JText::_('COM_COMMUNITY_PAGES_MEMBER') . ' <span class="joms-text--light">' . $membersCount . '</span>'
                                : JText::_('COM_COMMUNITY_PAGES_MEMBERS') . ' <span class="joms-text--light">' . $membersCount . '</span>'; ?>
                        </a>
                    </li>

                    <li class="hidden-desktop">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id); ?>">
                            <?php echo ($reviewsCount == 1)
                                ? JText::_('COM_COMMUNITY_PAGES_REVIEW') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'
                                : JText::_('COM_COMMUNITY_PAGES_REVIEWS') . ' <span class="joms-text--light">' . $reviewsCount . '</span>'; ?>
                        </a>
                    </li>
                                        
                    <?php if ((($isAdmin) || ($isMine) || ($isMember && !$isBanned)) && $isFile) { ?>
                        <li class="hidden-desktop">
                            <a href="javascript:" onclick="joms.api.fileList('page',<?php echo $page->id ?>)">
                                <?php echo ($isFile == 1)
                                    ? JText::_('COM_COMMUNITY_PAGES_FILE_COUNT') . ' <span class="joms-text--light">' . $isFile . '</span>'
                                    : JText::_('COM_COMMUNITY_PAGES_FILE_COUNT_MANY') . ' <span class="joms-text--light">' . $isFile . '</span>'; ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($isAdmin || $isSuperAdmin) { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=banlist&list=-1&pageid='.$page->id) ?>">
                                <?php echo JText::_('COM_COMMUNITY_PAGES_BANNED_MEMBERS') ; ?>
                                <span class="joms-text--light"><?php echo $totalBannedMembers; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>

            <?php if(!$waitingApproval && !$isBanned) { ?>
                <li class="half liked">
                    <a href="javascript:"
                       class="joms-js--like-pages-<?php echo $page->id; ?><?php echo $isUserLiked > 0 ? ' liked' : ''; ?>"
                       onclick="joms.api.page<?php echo $isUserLiked > 0 ? 'Unlike' : 'Like' ?>('pages', '<?php echo $page->id; ?>');"
                       data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                       data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>">
                        <svg viewBox="0 0 14 20" class="joms-icon">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"></use>
                        </svg>
                        <span
                            class="joms-js--lang"><?php echo ($isUserLiked > 0) ? JText::_('COM_COMMUNITY_LIKED') : JText::_('COM_COMMUNITY_LIKE'); ?></span>
                        <span class="joms-text--light"> <?php echo $totalLikes ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

</div>
</div>
