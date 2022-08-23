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

$likeCountHTML = '';
if ($likeCount > 0) {
    $likeCountHTML = ' (' . $likeCount . ')';
}

$isPhotoModal = $config->get('album_mode') == 1;
$location = $album->get('location');
$enableAlbumLocation = (!empty($location) && $config->get('enable_photos_location') == 1);

$enableReporting = false;
if ( $config->get('enablereporting') == 1 && ( $my->id > 0 || $config->get('enableguestreporting') == 1 ) ) {
    $enableReporting = true;
}

$isAuthorized = false;
if ( ( COwnerHelper::isCommunityAdmin() ) ||
     ( $album->creator == $my->id ) ||
     ( isset($pageId) && $pageId && $my->authorise('community.create', 'pages.photos.' . $pageId) ) ||
     ( isset($groupId) && $groupId && $my->authorise('community.create', 'groups.photos.' . $groupId) ) ||
     ( isset($eventId) && $eventId && $my->authorise('community.create', 'events.photos.' . $eventId) )
) {
    $isAuthorized = true;
}

$context = '';
$contextId = '';

if (isset($pageId) && $pageId > 0) {
    $contextId = $pageId;
    $context = 'page';
} else if (isset($groupId) && $groupId > 0) {
    $contextId = $groupId;
    $context = 'group';
} else if (isset($eventId) && $eventId > 0) {
    $contextId = $eventId;
    $context = 'event';
}
$enableReaction = !!$config->get('enablereaction');
?>

<!-- Photo listing -->
<div class="joms-page">
    <div class="joms-page__actions">
        <div class="joms-page__action">
            <h3 class="joms-text--title"><?php echo CStringHelper::escape(ucfirst($album->name)); ?></h3>
        </div>
        <?php if ( $isAuthorized && !CAlbumsHelper::isFixedAlbum($album) ) { ?>
        <div class="joms-page__action">
            <button class="joms-button--primary joms-button--small"
                    onclick="joms.api.photoUpload('<?php echo $album->id; ?>', '<?php echo $contextId; ?>', '<?php echo $context; ?>');">
                <?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS_TO_ALBUM'); ?>
            </button>
        </div>
        <?php } ?>
    </div>

    <?php echo $submenu; ?>

    <?php if ($photos) { ?>
        <ul class="joms-list--photos joms-js--album-<?php echo $album->id ?>">
            <?php

                $i = 0;
                for ($j = 0; $j < count($photos); $j++) {
                    $row =& $photos[$j];
                    if (!is_object($row)) {
                        continue;
                    }

                    $photoUrl = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $row->albumid . '&photoid=' . $row->id . ( $groupId ? '&groupid=' . $groupId : '' ) . ( $eventId ? '&eventid=' . $eventId : '' ) . ( $pageId ? '&pageid=' . $pageId : '' ));
                    if ( $isPhotoModal ) {
                        $photoUrl = 'javascript:';
                    }
            ?>
                <li class="joms-list__item joms-js--photo-<?php echo $row->id ?>" title="<?php echo $this->escape($row->caption); ?>" data-album="<?php echo $row->albumid ?>" data-id="<?php echo $row->id; ?>">
                    <a href="<?php echo $photoUrl ?>">
                        <img src="<?php echo $row->getThumbURI(); ?>" alt="<?php echo $this->escape($row->caption); ?>">
                    </a>
                    <div class="joms-gallery__options">
                        <a href="javascript:" class="joms-button--options" data-ui-object="joms-dropdown-button">
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-arrow-down"></use>
                            </svg>
                        </a>
                        <ul class="joms-dropdown">
                            <li><a href="<?php echo $row->getImageURI(); ?>" target="_blank" onclick="window.open('<?php echo $row->getImageURI(); ?>');return false;"><?php echo JText::_('COM_COMMUNITY_DOWNLOAD_IMAGE'); ?></a></li>
                            <?php if ( $my->authorise('community.delete', 'photos.' . $row->id, $row)) { ?>
                            <li><a href="javascript:" onclick="joms.api.photoRemove('<?php echo $row->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PHOTOS_DELETE'); ?></a></li>
                            <?php } ?>
                            <?php if ( $isAuthorized && !CAlbumsHelper::isFixedAlbum($album) ) { ?>
                            <li><a href="javascript:" onclick="joms.api.photoSetAlbum(['<?php echo $row->id; ?>'], '<?php echo $groupId; ?>', '<?php echo $pageId; ?>');"><?php echo JText::_('COM_COMMUNITY_MOVE_TO_ANOTHER_ALBUM'); ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>

            <?php } ?>
        </ul>
        <?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
            <div class="joms-pagination">
                <?php echo $pagination->getPagesLinks(); ?>
            </div>
        <?php } ?>

        <?php if ($people): ?>
            <div class="cMedia-TagPeople">
                <strong><?php echo JText::_('COM_COMMUNITY_PHOTOS_IN_THIS_ALBUM'); ?> </strong>
                <?php $totalpeople = sizeof($people);
                    $count = 1;
                    foreach ($people as $peep):?>
                        <a href="<?php echo CRoute::_(
                            'index.php?option=com_community&view=profile&userid=' . $peep->id
                        ); ?>" rel="nofollow"><?php echo $peep->getDisplayName(); ?><?php if ($count < $totalpeople) {
                                echo ",";
                            } ?></a>
                        <?php
                        $count++;
                    endforeach;
                ?>
            </div>
        <?php endif; ?>

        <div class="joms-gap"></div>
        <?php if ($enableReaction && $config->get('enablewalllikereaction')): ?>
            <?php if ($my->id): ?>
            <?php 
            $reactId = CLikesHelper::getReactId('album', $album->id, $my->id);
            
            $like = new CLike;
            $reactHtml = $like->showWhoReacts('album', $album->id);
            $show = $reactHtml ? '' : 'style="display:none"';
            ?>

            <div class="joms-stream__actions joms-album__reaction--btn">
                <?php if ($config->get('enablewalllikereaction')) echo CLikesHelper::renderReactionButton('page', 'album', $album->id, $reactId) ?>

                <?php if ($config->get('enablesharethis') == 1): ?>
                    <a href="javascript:;" onclick="joms.api.pageShare('<?php echo CRoute::getURI(); ?>')">
                        <?php echo JText::_('COM_COMMUNITY_SHARE'); ?>
                    </a>
                <?php endif ?>

                <?php if ($enableReporting): ?>
                    <a href="javascript:;" onclick="joms.api.photoReport('<?php echo $album->id ?>');">
                        <?php echo JText::_('COM_COMMUNITY_REPORT') ?>
                    </a>
                <?php endif ?>
            </div>
            <?php if ($config->get('enablewalllikereaction')) { ?>
                <div class="joms-stream__status" <?php echo $show ?>>
                    <?php echo $like->showWhoReacts('album', $album->id) ?>
                </div>
            <?php } ?>
            
            <?php endif ?>
        <?php else: ?>
            <?php if ($config->get('likes_photo') && $config->get('enablewalllikereaction')) { ?>
            <button class="joms-button--<?php echo $likeLiked ? 'primary' : 'neutral' ?> joms-button--small joms-js--like-album-<?php echo $album->id; ?>"
                    onclick="joms.api.page<?php echo $likeLiked ? 'Unlike' : 'Like' ?>('album', '<?php echo $album->id ?>');"
                    data-lang="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                    data-lang-like="<?php echo JText::_('COM_COMMUNITY_LIKE'); ?>"
                    data-lang-liked="<?php echo JText::_('COM_COMMUNITY_LIKED'); ?>"><?php
                echo JText::_( $likeLiked ? 'COM_COMMUNITY_LIKED' : 'COM_COMMUNITY_LIKE' ); ?><?php echo $likeCountHTML; ?></button>
            <?php } ?>

            <?php if ($config->get('enablesharethis') == 1) { ?>
            <button class="joms-button--neutral joms-button--small" onclick="joms.api.pageShare('<?php echo CRoute::getURI(); ?>')"><?php echo JText::_('COM_COMMUNITY_SHARE'); ?></button>
            <?php } ?>

            <?php if ($enableReporting) { ?>
            <button class="joms-button--neutral joms-button--small" onclick="joms.api.photoReport('<?php echo $my->id ?>');"><?php echo JText::_('COM_COMMUNITY_REPORT') ?></button>
            <?php } ?>
        <?php endif ?>

    <?php } else { ?>
        <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_PHOTOS_UPLOADED'); ?></div>
    <?php } ?>

</div>

<div class="joms-gap"></div>

<?php if ($photos) { ?>

    <?php if ( $enableAlbumLocation || !empty($otherAlbums) ) { ?>

        <div class="joms-sidebar">

            <div class="joms-module__wrapper--stacked">

                 <?php if ($enableAlbumLocation && $photosmapdefault) { ?>
                    <!-- album location module -->
                    <div class="joms-module--stacked">
                        <div class="joms-tab__bar">
                            <a class="active"><?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_TAKEN_AT_DESC',$album->location); ?></a>
                        </div>
                        <div class="joms-tab__content">
                            <div class="joms-js--album-map-wrapper">
                                <div class="app-box-content event-description">
                                    <!-- begin: dynamic map -->
                                    <?php
                                    if (CFactory::getConfig()->get('maps_api', '') == 'openstreetmap') { 
                                        echo CMapping::drawOSMMap($album->location, '100%', '100%'); 
                                    } else { ?>
                                        <div class="joms-js--album-map" style="height:210px;width:100%;">
                                            <?php echo JText::_('COM_COMMUNITY_MAPS_LOADING'); ?>
                                        </div>
                                    <?php } ?>
                                    
                                    <!-- end: dynamic map -->
                                    <div class="joms-text--small"><?php echo CMapping::getFormatedAdd($album->location); ?></div>
                                </div>
                                <div class="joms-module__footer">
                                    <?php if (CFactory::getConfig()->get('maps_api', '') == 'googlemap') { ?>
                                        <a href="http://maps.google.com/?q=<?php echo urlencode($album->location); ?>" target="_blank" class="joms-button--link"><?php echo JText::_('COM_COMMUNITY_EVENTS_FULL_MAP'); ?></a>
                                    <?php } else { ?>
                                        <a href="https://www.openstreetmap.org/?mlat=<?php echo $album->latitude ?>&mlon=<?php echo $album->longitude ?>#map=9/<?php echo $album->latitude ?>/<?php echo $album->longitude ?>" target="_blank" class="joms-button--link"><?php echo JText::_('COM_COMMUNITY_EVENTS_FULL_MAP'); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <script>
                                (function( w ) {
                                    w.joms_queue || (w.joms_queue = []);
                                    w.joms_queue.push(function() {
                                        var address, container, geocoder;

                                        // Disable map on mobile.
                                        if ( joms.mobile ) {
                                            joms.jQuery('.joms-js--album-map-wrapper').remove();
                                            return;
                                        }

                                        address = '<?php echo addslashes($album->location); ?>',
                                        container = joms.jQuery('.joms-js--album-map');

                                        // Delay execution.
                                        setTimeout(function() {
                                            joms.util.map(function() {
                                                geocoder = new google.maps.Geocoder();
                                                geocoder.geocode({ address: address }, function( results, status ) {
                                                    var location, center, mapOptions, map, marker;

                                                    if (status != google.maps.GeocoderStatus.OK) {
                                                        container.html('<?php echo JText::_("COM_COMMUNITY_GMAP_NO_API_KEY", TRUE); ?>');
                                                        return;
                                                    }

                                                    location = results[0].geometry.location;
                                                    center = new google.maps.LatLng( location.lat(), location.lng() );

                                                    mapOptions = {
                                                        zoom: 14,
                                                        center: center,
                                                        mapTypeId: google.maps.MapTypeId.ROADMAP
                                                    };

                                                    map = new google.maps.Map( container[0], mapOptions );

                                                    marker = new google.maps.Marker({
                                                        map: map,
                                                        position: center
                                                    });

                                                });
                                            });
                                        }, 2000 );

                                    });
                                })( window );
                            </script>
                        </div>
                    </div>
                <?php } ?>

                    <!-- Other Album Section -->
                <?php if (!empty($otherAlbums)) { ?>
                    <div class="joms-module--stacked">
                        <div class="joms-tab__bar">
                            <a href="javascript:void(0)" class="active">
                                <?php
                                if($groupId){
                                    echo JText::_('COM_COMMUNITY_PHOTOS_GROUP_OTHER_ALBUMS');
                                }elseif($eventId){
                                    echo JText::_('COM_COMMUNITY_PHOTOS_EVENT_OTHER_ALBUMS');
                                }else{
                                    echo JText::_('COM_COMMUNITY_PHOTOS_OTHER_ALBUMS');
                                }
                                ?>
                            </a>
                        </div>
                        <div class="joms-tab__content">
                            <ul class="joms-gallery">
                                <?php foreach ($otherAlbums as $others) { ?>
                                    <?php $albumURL = CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $others->id . ( $groupId > 0 ? '&groupid=' . $groupId : '' )); ?>
                                    <li class="joms-gallery__item half">
                                        <div class="joms-gallery__thumbnail">
                                            <a href="<?php echo $albumURL; ?>">
                                                <img src="<?php echo $others->getCoverThumbURI(); ?>" alt="photo thumbnail">
                                            </a>
                                        </div>
                                        <div class="joms-gallery__body">
                                            <a href="<?php echo $albumURL; ?>" class="joms-gallery__title">
                                                <?php echo $this->escape($others->name); ?>
                                            </a>

                                            <div class="joms-gallery__count" style="display: block;">
                                                <small>
                                                    <?php if (CStringHelper::isPlural($others->count)) {
                                                        echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNTER', $others->count);
                                                    } else {
                                                        echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNTER_SINGULAR', $others->count);
                                                    } ?>
                                                </small>
                                            </div>

                                        </div>

                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } //end if ?>

            </div>
        </div>

    <?php } ?>

    <div class="<?php echo ($enableAlbumLocation || !empty($otherAlbums)) ? 'joms-main' : 'joms-main--full'; ?>">
        <div class="joms-page">
            <div class="cMedia-Author">
                <span>
                    <?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_LAST_UPDATED', $album->lastUpdated); ?>
                    <?php echo JText::_('COM_COMMUNITY_BY'); ?>
                    <a href="<?php echo CUrlHelper::userLink($owner->id); ?>"><?php echo $owner->getDisplayName(); ?></a>
                </span><br>
                <?php if($enableAlbumLocation){ ?>
                <span><?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_TAKEN_AT_DESC', $album->get('location')); ?></span>
                <?php } ?>
            </div>

            <?php

                $description = $album->get('description');
                $excerpt = JHTML::_('string.truncate', $description, 300);

            ?>

            <br>
            <div>
                <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC') ?></h5>
                <div class="joms-js--desc-excerpt"><?php echo nl2br($excerpt); ?></div>
                <?php if ($description != $excerpt) { ?>
                <div class="joms-js--desc-fulltext" style="display:none;"><?php echo nl2br($description); ?></div>
                <button class="joms-button--neutral joms-js--btn-more" style="margin-top:5px;"><?php echo JText::_("COM_COMMUNITY_SHOW_MORE"); ?></button>
                <?php } ?>
            </div>
            <script>
                joms.onStart(function( $ ) {
                    var $excerpt = $('.joms-js--desc-excerpt'),
                        $fulltext = $('.joms-js--desc-fulltext'),
                        $btn = $('.joms-js--btn-more'),
                        langMore = '<?php echo JText::_("COM_COMMUNITY_SHOW_MORE") ?>',
                        langLess = '<?php echo JText::_("COM_COMMUNITY_SHOW_LESS") ?>';

                    $btn.on( 'click', function() {
                        if ( $excerpt.is(':visible') ) {
                            $excerpt.hide();
                            $fulltext.show();
                            $btn.html( langLess );
                        } else {
                            $excerpt.show();
                            $fulltext.hide();
                            $btn.html( langMore );
                        }
                    });
                });
            </script>

            <br>
            <div>
                <?php if ($config->get('enablewallcomment')) { ?>
                    <h5 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_COMMENTS') ?></h5>
                    <?php if (!$enableReaction): ?>
                    <div class="joms-stream__status--mobile">
                        <a href="javascript:" onclick="joms.api.streamShowComments('<?php echo $album->id ?>', 'albums');">
                            <span class="joms-comment__counter--<?php echo $album->id; ?>"><?php echo $wallCount; ?></span>
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-bubble"></use>
                            </svg>
                        </a>
                    </div>
                    <?php endif ?>
                    
                    <div style="display:none"><?php echo $wallViewAll; ?></div>
                    <?php echo $wallContent; ?>
                    <?php echo empty($wallForm) ? '' : $wallForm; ?>
                <?php } ?>
            </div>
            <script>
                (function( w ) {
                    w.joms_queue || (w.joms_queue = []);
                    w.joms_queue.push(function( $ ) {
                        $('.joms-js--comments').prepend( $('.joms-js--more-comments').parent().html() );
                    });
                })( window );
            </script>





        </div>
    </div>

<?php } ?>

<script>
joms.onStart(function( $ ) {
    var links = $('.joms-list--photos .joms-list__item').children('a'),
        timeoutid,
        isClick = true,
        before = {},
        after = {};
    
    links.on('mousedown', function(e) {
        if (e.which === 3) {
            return;
        }
        before.x = e.pageX;
        before.y = e.pageY;
        timeoutid = setTimeout(function() {
            isClick = false;
        }, 300);
    }).on('mouseup', function(e) {
        if (e.which === 3) {
            return;
        }
        after.x = e.pageX;
        after.y = e.pageY;
        clearTimeout(timeoutid);
        if (isClick && Math.abs(after.x - before.x) < 10 && Math.abs(after.y - before.y) < 10) {
            var el = $(this).parent();
            var album = el.data('album');
            var id = el.data('id');
            joms.api.photoOpen(album, id);
        } else {
            isClick = true;
        }
        
        before = {};
        after = {};
    });
});
</script>

<?php if ($isAuthorized) { ?>
<script>
// Photo sorting..
joms.onStart(function( $ ) {
    var saveList = joms._.debounce(function() {
        var $list = $('.joms-list--photos').children('.joms-list__item'),
            albumid = '<?php echo $album->id ?>',
            ids = [];

        $list.each(function() {
            ids.push( 'photoid[]=' + $(this).data('id') );
        });

        joms.ajax({
            func: 'photos,ajaxSaveOrdering',
            data: [ ids.join('&'), albumid ],
            callback: function( json ) {
            }
        });

    }, 800 );

    joms.util.loadLib( 'dragsort', function() {
        $('.joms-list--photos').dragsort({
            dragSelector: 'li img',
            dragEnd: function() {
                saveList();
            },
            placeHolderTemplate: '<li class="joms-list__item joms-list__item--dragged"><img style="visibility:hidden"></li>',
            dragBetween: false
        });
    });
});
</script>
<?php }
