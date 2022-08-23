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
?>

<?php

    $my = CFactory::getUser();
    $pageModel = CFactory::getModel('pages');

    for ( $i = 0; $i < count( $pages ); $i++ ) {
        $page =& $pages[$i];

        $isMine = $my->id == $page->ownerid;
        $isAdmin = $pageModel->isAdmin($my->id, $page->id);
        $isMember = $pageModel->isMember($my->id, $page->id);
        $isBanned = $page->isBanned($my->id);
        $creator = CFactory::getUser($page->ownerid);

        // Check if "Feature this" button should be added or not.
        $addFeaturedButton = false;
        $isFeatured = false;
        if ($isCommunityAdmin && $showFeatured) {
            $addFeaturedButton = true;
            if (in_array($page->id, $featuredList)) {
                $isFeatured = true;
            }
        }

        //all the information needed to fill up the summary
        $params = $page->getParams();

        $videoModel = CFactory::getModel('videos');
        $showVideo = ($params->get('videopermission') != -1) && $config->get('enablevideos') && $config->get('pagevideos');
        if ($showVideo) {
            $videoModel->getPageVideos($page->id, '',
                $params->get('pagerecentvideos', PAGE_VIDEO_RECENT_LIMIT));
            $totalVideos = $videoModel->total ? $videoModel->total : 0;
        }

        $showPhoto = ($params->get('photopermission') != -1) && $config->get('enablephotos') && $config->get('pagephotos');
        $photosModel = CFactory::getModel('photos');
        $albums = $photosModel->getPageAlbums($page->id, true, false,
            $params->get('pagerecentphotos', PAGE_PHOTO_RECENT_LIMIT));
        $totalPhotos = 0;
        foreach ($albums as $album) {
            $albumParams = new CParameter($album->params);
            $totalPhotos = $totalPhotos + $albumParams->get('count');
        }
        
        $pollModel = CFactory::getModel('polls');
        $polls = $pollModel->getAllPolls(null, null, null, null, false, true, null, null, $page->id);
        $totalPolls = 0; 
        foreach ($polls as $poll) {
            $totalPolls++;
        }
        $showPolls = ($config->get('page_polls') && $config->get('enablepolls') && $params->get('pollspermission',
            1) >= 1);

        // Check if "Invite friends" and "Settings" buttons should be added or not.
        $canInvite = false;
        $canEdit = false;

        if (($isMember && !$isBanned) || $isCommunityAdmin) {
            $canInvite = true;
            if ($isMine || $isAdmin || $isCommunityAdmin) {
                $canEdit = true;
            }
        }
    }
?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_PAGES_MY_INVITES'); ?></h3>
        </div>

        <div class="joms-list__utilities">
            <form method="GET" class="joms-inline--desktop"
                  action="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search"
                       placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_PAGE_PLACEHOLDER'); ?>">
                </span>
                <?php echo JHTML::_('form.token') ?>
                <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                <input type="hidden" name="option" value="com_community"/>
                <input type="hidden" name="view" value="pages"/>
                <input type="hidden" name="task" value="search"/>
                <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
            </form>
        </div>
    </div>

    <?php if($submenu){ ?>
        <?php echo $submenu;?>
        <div class="joms-gap"></div>
    <?php } ?>

    <?php echo $sortings; ?>
    <div class="joms-gap"></div>
    <?php
    if( $pages )
    {
    ?>
    <div class="joms-alert joms-alert--info">
        <?php echo JText::sprintf( CStringHelper::isPlural( $count ) ? 'COM_COMMUNITY_PAGES_INVIT_COUNT_MANY' : 'COM_COMMUNITY_PAGES_INVIT_COUNT' , $count ); ?>
    </div>

    <ul class="joms-list--card">
    <?php
        for( $i = 0; $i < count( $pages ); $i++ )
        {
            $page  =& $pages[$i];
    ?>
        <li id="pages-invite-<?php echo $page->id;?>" class="joms-list__item">
            <div class="joms-list__cover">
                <a href="<?php echo $page->getLink(); ?>">
                    <div class="joms-list__cover-image" data-image="<?php echo $page->getCover(); ?>" style="background-image: url(<?php echo $page->getCover(); ?>);"></div>
                </a>
            </div>

            <div class="joms-list__content">
                <h4 class="joms-list__title">
                    <a href="<?php echo CRoute::_( 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id );?>"><?php echo $page->name; ?></a>
                </h4>

                <ul class="joms-list--table">
                    <?php if(($page->approvals == COMMUNITY_PRIVATE_PAGE && $isMember) || $page->approvals == COMMUNITY_PUBLIC_PAGE){ ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid='.$page->id) ?>">
                        <?php echo JText::sprintf((CStringHelper::isPlural($page->membercount)) ? 'COM_COMMUNITY_PAGES_MEMBER_COUNT_MANY':'COM_COMMUNITY_PAGES_MEMBER_COUNT', $page->membercount);?>
                        </a>
                    </li>

                    <?php if($showVideo){ ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-film"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&pageid=' . $page->id); ?>">
                        <?php echo ($totalVideos == 1)
                            ? $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT')
                            : $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY'); ?>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if($showPhoto){ ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-image"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&pageid=' . $page->id); ?>">
                            <?php echo ($totalPhotos == 1) ?
                                $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') :
                                $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT'); ?>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if ($showPolls) { ?>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-list"></use>
                            </svg>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pageid=' . $page->id); ?>">
                                <?php echo ($totalPolls == 1 || $totalPolls == 0)
                                    ? $totalPolls.' '.JText::_('COM_COMMUNITY_POLLS_COUNT')
                                    : $totalPolls.' '.JText::_('COM_COMMUNITY_POLLS_COUNT_MANY'); ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php } ?>
                </ul>

                <div class="joms-js--invitation-notice-page-<?php echo $page->id; ?>"></div>
            </div>

            <div class="joms-list__footer joms-padding">
                <div class="<?php echo CUserHelper::onlineIndicator($creator); ?>">
                <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id);?>"><img src="<?php echo $creator->getAvatar();?>" alt="avatar" data-author="<?php echo $creator->id; ?>" ></a>
                </div>
                <?php echo JText::_('COM_COMMUNITY_PAGES_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($creator->id);?>"><?php echo $creator->getDisplayName(); ?></a>
            </div>

            <span class="joms-list__permission joms-js--invitation-buttons-page-<?php echo $page->id; ?>">
                <a class="joms-button--neutral joms-button--smallest" href="javascript:" onclick="joms.api.invitationReject('page', '<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?></a>
                <a class="joms-button--primary joms-button--smallest" href="javascript:" onclick="joms.api.invitationAccept('page', '<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?></a>
            </span>
        </li>
    <?php
        }
    ?>
    </ul>

    <script>
    // window.joms_queue || (window.joms_queue = []);
    // window.joms_queue.push(function( $ ) {
    //     $('.joms-list__cover-image').each(function( index, el ) {
    //         el = $( el );
    //         el.data('image') && el.backstretch( el.data('image') );
    //     });
    // });
    </script>

    <?php
    }else
    {
    ?>
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_PAGES_NO_INVITATIONS'); ?></div>
    <?php
    }
    ?>

    <?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
        <div class="joms-pagination">
            <?php echo $pagination->getPagesLinks(); ?>
        </div>
    <?php } ?>
</div>
