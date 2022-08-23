<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') OR DIE();

// photo albums
$album = array_map(function($a) {
  $a->id = (string) $a->id;
  return $a;
}, $album);

// Poll categories
$rawPollCats = CFactory::getModel('polls')->getCategories();
$pollCategories = array_map(function($cat) {
  $item = new stdClass;
  $item->id = (string) $cat->id;
  $item->name = JText::_($cat->name);
  $item->parent = (string) $cat->parent;
  return $item;
}, $rawPollCats);

// Event categories.
$rawEventCategories = CFactory::getModel('events')->getCategories();
$eventCategories = array();
if ( count($rawEventCategories) >= 1 ) {
  foreach ($rawEventCategories as $index => $value) {
    $eventCategories[] = array(
      'id' => (string) $value->id,
      'name' => JText::_( $value->name )
    );
  }
}

// Video categories.
$rawVideoCategories = CFactory::getModel('videos')->getAllCategories();
$rawVideoCategories = CCategoryHelper::getCategories($rawVideoCategories);
$nodeText = array();
foreach ( $rawVideoCategories as $key => $row ) {
  $nodeText[$key]  = $row['nodeText'];
}
array_multisort(array_map('strtolower', $nodeText), SORT_ASC, $rawVideoCategories);

$videoCategories = array();
if ( count($rawVideoCategories) >= 1 ) {
  foreach ($rawVideoCategories as $index => $value) {
    $videoCategories[] = array(
      'id' => (string) $value['id'],
      'name' => JText::_( $value['name'] ),
      'parent' => (string) $value['parent']
    );
  }
}
$config = CFactory::getConfig();
$isProfile = ($type == 'profile') ? 1 : 0;
$isMyProfile = ($my->id == $target) ? 1 : 0;
$isPage = ($type == 'pages') ? 1 : 0;
$isGroup = ($type == 'groups') ? 1 : 0;
$isEvent = ($type == 'events') ? 1 : 0;
$isAdmin = (COwnerHelper::isCommunityAdmin() && $target == $my->id) ? 1 : 0;
$num_file_per_upload = 8;
$num_file_per_upload = ($isProfile || $isMyProfile) ? $config->get("file_sharing_limit_per_upload", 8) : $num_file_per_upload;
$num_file_per_upload = $isPage ? $config->get("file_sharing_limit_per_upload_page", 8) : $num_file_per_upload;
$num_file_per_upload = $isGroup ? $config->get("file_sharing_limit_per_upload_group", 8) : $num_file_per_upload;
$num_file_per_upload = $isEvent ? $config->get("file_sharing_limit_per_upload_event", 8) : $num_file_per_upload;

$num_photo_per_upload = $config->get("num_photo_per_upload_stream", 8);
$profiledefaultprivacy = CFactory::getUser($my->id)->getParams()->get("privacyProfileView");
$profiledefaultprivacy = $profiledefaultprivacy && $profiledefaultprivacy !== 'undefined' ? $profiledefaultprivacy : '10';
$max_input_time = ini_get('max_input_time');
?>

<script>
  joms || (joms = {});
  joms.constants || (joms.constants = {});
  joms.language || (joms.language = {});

  joms.constants.uid                          = '<?php echo $my->id; ?>';

  joms.constants.album                        = <?php echo json_encode( $album ); ?>;
  joms.constants.eventCategories              = <?php echo json_encode( $eventCategories ); ?>;
  joms.constants.videoCategories              = <?php echo json_encode( $videoCategories ); ?>;
  joms.constants.pollCategories               = <?php echo json_encode( $pollCategories ); ?>;
  joms.constants.customActivities             = <?php echo json_encode( CActivityStream::getCustomActivities() ); ?>;
  joms.constants.videocreatortype             = '<?php echo VIDEO_USER_TYPE ?>';

  joms.constants.juri || (joms.constants.juri = {});
  joms.constants.juri.base                    = '<?php echo JURI::base(); ?>';
  joms.constants.juri.root                    = '<?php echo JURI::root(); ?>';

  joms.constants.settings || (joms.constants.settings = {});
  joms.constants.settings.isProfile           = <?php echo $isProfile ?>;
  joms.constants.settings.isMyProfile         = <?php echo $isMyProfile ?>;
  joms.constants.settings.isPage              = <?php echo $isPage ?>;
  joms.constants.settings.isGroup             = <?php echo $isGroup ?>;
  joms.constants.settings.isEvent             = <?php echo $isEvent ?>;
  joms.constants.settings.isAdmin             = <?php echo $isAdmin ?>;

  joms.constants.conf || (joms.constants.conf = {});

  joms.constants.conf.statusmaxchar           = +'<?php echo $config->get("statusmaxchar"); ?>';
  joms.constants.conf.profiledefaultprivacy   = '<?php echo $profiledefaultprivacy ?>';
  joms.constants.conf.maxvideouploadsize      = +'<?php echo $config->get("maxvideouploadsize"); ?>';
  joms.constants.conf.maxuploadsize           = +'<?php echo $config->get("maxuploadsize"); ?>';
  joms.constants.conf.enablephotos            = +'<?php echo $permission->enablephotos; ?>';
  joms.constants.conf.num_photo_per_upload    = +'<?php echo $num_photo_per_upload ?>';
  joms.constants.conf.enablephotosgif         = +'<?php echo $config->get("enable_animated_gif"); ?>';
  joms.constants.conf.enablevideos            = +'<?php echo $permission->enablevideos; ?>';
  joms.constants.conf.enablevideosupload      = +'<?php echo $permission->enablevideosupload; ?>';
  joms.constants.conf.enablevideosmap         = +'<?php echo $config->get("videosmapdefault");?>';
  joms.constants.conf.enableevents            = +'<?php echo $permission->enableevents; ?>';
  joms.constants.conf.enablecustoms           = +'<?php echo $config->get("custom_activity") ? "1" : "0"; ?>';
  joms.constants.conf.limitphoto              = +'<?php echo $config->get("limit_photos_perday");?>';
  joms.constants.conf.uploadedphoto           = +'<?php echo CFactory::getModel("photos")->getTotalToday($my->id); ?>';
  joms.constants.conf.enablemood              = +'<?php echo $config->get("enablemood"); ?>';
  joms.constants.conf.enablelocation          = +'<?php echo $config->get("streamlocation"); ?>';
  joms.constants.conf.limitvideo              = +'<?php echo $config->get("limit_videos_perday");?>';
  joms.constants.conf.uploadedvideo           = +'<?php echo CFactory::getModel("videos")->getTotalToday($my->id); ?>';
  joms.constants.conf.videoextensions         = '<?php echo $config->get("videoextensions", 'mp4'); ?>';
  joms.constants.conf.limitevent              = +'<?php echo $config->get("limit_events_perday");?>';
  joms.constants.conf.createdevent            = +'<?php echo CFactory::getModel("events")->getTotalToday($my->id); ?>';
  joms.constants.conf.eventshowampm           = +'<?php echo $config->get("eventshowampm");?>';
  joms.constants.conf.firstday                = +'<?php echo $config->get("event_calendar_firstday") == "Monday" ? 1 : 0; ?>';
  joms.constants.conf.enablefiles             = +'<?php echo $permission->enablefiles; ?>'; 
  joms.constants.conf.limitfile               = +'<?php echo $config->get('limit_files_perday'); ?>'; 
  joms.constants.conf.uploadedfile            = +'<?php echo CFactory::getModel("files")->getTotalToday($my->id); ?>';
  joms.constants.conf.num_file_per_upload     = +'<?php echo $num_file_per_upload; ?>';
  joms.constants.conf.file_sharing_activity   = +'<?php echo $config->get("file_sharing_activity", 0) ?>';
  joms.constants.conf.file_sharing_activity_max = +'<?php echo $config->get("file_sharing_activity_max", 1) ?>';
  joms.constants.conf.file_activity_ext       = '<?php echo $config->get("file_sharing_activity_ext", "zip") ?>';
  joms.constants.conf.file_sharing_group      = +'<?php echo $config->get("file_sharing_group", 0) ?>';
  joms.constants.conf.file_sharing_group_max  = +'<?php echo $config->get("filemaxuploadsize", 1) ?>';
  joms.constants.conf.file_sharing_page      = +'<?php echo $config->get("file_sharing_page", 0) ?>';
  joms.constants.conf.file_sharing_page_max  = +'<?php echo $config->get("pagefilemaxuploadsize", 1) ?>';
  joms.constants.conf.file_group_ext          = '<?php echo $config->get("file_sharing_group_ext", "zip") ?>';
  joms.constants.conf.file_page_ext          = '<?php echo $config->get("file_sharing_page_ext", "zip") ?>';
  joms.constants.conf.file_sharing_event      = +'<?php echo $config->get("file_sharing_event", 0) ?>';
  joms.constants.conf.file_sharing_event_max  = +'<?php echo $config->get("file_sharing_event_max", 1) ?>';
  joms.constants.conf.file_event_ext          = '<?php echo $config->get("file_sharing_event_ext", "zip") ?>';
  joms.constants.conf.enablepolls             = +'<?php echo $permission->enablepolls; ?>';
  joms.constants.conf.enablebackground        = +'<?php echo $config->get("enablebackground"); ?>';
  joms.constants.conf.maps_api                =  '<?php echo $config->get("maps_api") ?>';
  joms.constants.conf.max_input_time          = <?php echo (int) $max_input_time ?>

  joms.constants.postbox || (joms.constants.postbox = {});
  joms.constants.postbox.attachment           = {};
  joms.constants.postbox.attachment.element   = '<?php echo $type ?>';
  joms.constants.postbox.attachment.target    = '<?php echo $target ?>';
  
 

  <?php if(JFactory::getApplication()->input->get('view') == 'profile'){ ?>
  joms.constants.postbox.attachment.filter   = 'active-profile';
  <?php } ?>

<?php
  $moodList = array();
  $moodList[] = array(
      'id' => '',
      'title' => JText::_('COM_COMMUNITY_POSTBOX_NONE_MOOD'),
      'description' => JText::_('COM_COMMUNITY_POSTBOX_NONE_MOOD_DESC'),
      'custom' => true,
      'image' => JUri::root() . 'components/com_community/assets/photos/none.png',
  );
  foreach ($moods as $key => $value) {
      if(!$value->custom) continue;

      $moodList[] = array(
          'id'          => $value->id,
          'title'       => $value->title,
          'description' => $value->description,
          'custom'      => $value->custom ? true : false,
          'image'       => $value->custom ? $value->image : null
      );
  }
  ?>
 joms.constants.moods = <?php echo json_encode($moodList) ?>;

<?php
$bgs = array();
$bgs[] = array(
    'id' => '0',
    'title' => 'None',
    'description' => '',
    'image' => '',
    'thumbnail' => JUri::root() . 'components/com_community/assets/photos/none.png',
    'textcolor' => '',
    'placeholdercolor' => '',
);

foreach ($backgrounds as $key => $value) {
    $background = array(
        'id' => $value->id,
        'title' => $value->title,
        'description' => $value->description,
        'image' => $value->image,
        'thumbnail' => $value->thumb,
        'textcolor' => $value->textcolor,
        'placeholdercolor' => $value->placeholdercolor
    );

    $bgs[] = $background;
}
?>
    joms.constants.backgrounds = <?php echo json_encode($bgs) ?>;
  

  joms.language.cancel                        = '<?php echo addslashes( JText::_("COM_COMMUNITY_CANCEL") ); ?>';
  joms.language.remove                        = '<?php echo addslashes( JText::_("COM_COMMUNITY_REMOVE") ); ?>';
  joms.language.saving                        = '<?php echo addslashes( JText::_("COM_COMMUNITY_SAVING") ); ?>';
  joms.language.yes                           = '<?php echo addslashes( JText::_("COM_COMMUNITY_YES") ); ?>';
  joms.language.no                            = '<?php echo addslashes( JText::_("COM_COMMUNITY_NO") ); ?>';
  joms.language.at                            = '<?php echo addslashes( JText::_("COM_COMMUNITY_AT") ); ?>';
  joms.language.next                          = '<?php echo addslashes( JText::_("COM_COMMUNITY_NEXT") ); ?>';
  joms.language.prev                          = '<?php echo addslashes( JText::_("COM_COMMUNITY_PREV") ); ?>';
  joms.language.select_category               = '<?php echo addslashes( JText::_("COM_COMMUNITY_SELECT_CATEGORY") ); ?>';
  joms.language.and                           = '<?php echo addslashes( JText::_("COM_COMMUNITY_AND") ); ?>';

  joms.language.status || (joms.language.status = {});
  joms.language.status['status_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT") ); ?>';
  joms.language.status['photo_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_PHOTO_HINT") ); ?>';
  joms.language.status['photos_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_PHOTOS_HINT") ); ?>';
  joms.language.status['file_hint']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_FILE_HINT") ); ?>';
  joms.language.status['poll_hint']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_POLL_HINT") ); ?>';
  joms.language.status['files_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_FILES_HINT") ); ?>';
  joms.language.status['video_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_VIDEO_HINT") ); ?>';
  joms.language.status['event_hint']          = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_EVENT_HINT") ); ?>';
  joms.language.status['custom_hint']         = '<?php echo addslashes( JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT") ); ?>';
  joms.language.status.mood                   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_MOOD") ); ?>';
  joms.language.status.remove_mood_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_REMOVE_MOOD_BUTTON") ); ?>';
  joms.language.status.location               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION") ); ?>';

  joms.language.postbox || (joms.language.postbox = {});
  joms.language.postbox.status                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS") ); ?>';
  joms.language.postbox.photo                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO") ); ?>';
  joms.language.postbox.gif                   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GIF") ); ?>';
  joms.language.postbox.video                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO") ); ?>';
  joms.language.postbox.event                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT") ); ?>';
  joms.language.postbox.file                  = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_FILE") ); ?>';
  joms.language.postbox.poll                  = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL") ); ?>';
  joms.language.postbox.custom                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM") ); ?>';
  joms.language.postbox.post_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POST_BUTTON") ); ?>';
  joms.language.postbox.cancel_button         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CANCEL_BUTTON") ); ?>';
  joms.language.postbox.upload_button         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_UPLOAD_BUTTON") ); ?>';
  joms.language.postbox.polltime              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_SELECT_EXPRIED_DATE") ); ?>';

  joms.language.photo || (joms.language.photo = {});
  joms.language.photo.batch_notice            = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_PHOTO_BATCH_NOTICE", $num_photo_per_upload) ); ?>';
  joms.language.photo.upload_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON") ); ?>';
  joms.language.photo.upload_button_more      = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON_MORE") ); ?>';
  joms.language.photo.upload_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_PHOTO_UPLOAD_LIMIT_EXCEEDED") ); ?>';
  joms.language.photo.max_upload_size_error   = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB", $config->get("maxuploadsize")) ); ?>';
  joms.language.photo.upload_button_2         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON_2") ); ?>';
  joms.language.photo.gif_upload_button       = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GIF_UPLOAD_BUTTON") ); ?>';
  joms.language.photo.drop_to_upload          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_PHOTO_DROP_TO_UPLOAD") ); ?>';

  joms.language.file || (joms.language.file = {});
  joms.language.file.upload_button            = '<?php echo addslashes( JText::_('COM_COMMUNITY_POSTBOX_FILE_UPLOAD_BUTTON')) ?>';
  joms.language.file.upload_button_more       = '<?php echo addslashes( JText::_('COM_COMMUNITY_POSTBOX_FILE_UPLOAD_BUTTON_MORE')) ?>';
  joms.language.file.file_type_not_permitted  = '<?php echo addslashes( JText::_('COM_COMMUNITY_POSTBOX_FILE_TYPE_NOT_PERMITTED')) ?>';
  joms.language.file.max_upload_size_error    = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB", '##maxsize##') ); ?>';
  joms.language.file.batch_notice             = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_FILE_BATCH_NOTICE", $num_file_per_upload) ); ?>';
  joms.language.file.drop_to_upload           = '<?php echo addslashes( JText::_('COM_COMMUNITY_POSTBOX_FILE_DROP_TO_UPLOAD') ) ?>';

  joms.language.video || (joms.language.video = {});
  joms.language.video.location                = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION") ); ?>';
  joms.language.video.category_label          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_CATEGORY_LABEL") ); ?>';
  joms.language.video.category_notice         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_CATEGORY_NOTICE") ); ?>';
  joms.language.video.url_notice              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_URL_NOTICE") ); ?>';
  joms.language.video.share_button            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_SHARE_BUTTON") ); ?>';
  joms.language.video.link_hint               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_LINK_HINT") ); ?>';
  joms.language.video.upload_title            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_TITLE") ); ?>';
  joms.language.video.upload_button           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_BUTTON") ); ?>';
  joms.language.video.drop_to_upload          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_DROP_TO_UPLOAD") ); ?>';
  joms.language.video.upload_hint             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_HINT") ); ?>';
  joms.language.video.upload_maxsize          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_MAXSIZE") ); ?>';
  joms.language.video.max_upload_size_error   = '<?php echo addslashes( JText::sprintf("COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB", $config->get("maxvideouploadsize")) ); ?>';
  joms.language.video.upload_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_UPLOAD_LIMIT_EXCEEDED") ); ?>';
  joms.language.video.select_category         = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_SELECT_CATEGORY") ); ?>';
  joms.language.video.invalid_url             = '<?php echo addslashes( JText::_("COM_COMMUNITY_VIDEO_INVALID_URL") ); ?>';
  joms.language.video.or                      = '<?php echo addslashes( JText::_("COM_COMMUNITY_OR") ); ?>';

  joms.language.event || (joms.language.event = {});
  joms.language.event.title_hint              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_TITLE_HINT") ); ?>';
  joms.language.event.date_and_time           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_DATE_AND_TIME") ); ?>';
  joms.language.event.event_detail            = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_DETAIL") ); ?>';
  joms.language.event.category                = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_CATEGORY") ); ?>';
  joms.language.event.location                = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION") ); ?>';
  joms.language.event.location_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION") ); ?>';
  joms.language.event.start                   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START") ); ?>';
  joms.language.event.start_date_hint         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_DATE_HINT") ); ?>';
  joms.language.event.start_time_hint         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_TIME_HINT") ); ?>';
  joms.language.event.end                     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END") ); ?>';
  joms.language.event.end_date_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT") ); ?>';
  joms.language.event.end_time_hint           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_TIME_HINT") ); ?>';
  joms.language.event.done_button             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_EVENT_DONE_BUTTON") ); ?>';
  joms.language.event.create_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_DAILY_LIMIT") ); ?>';
  joms.language.event.category_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_CATEGORY_NOT_SELECTED") ); ?>';
  joms.language.event.location_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_LOCATION_NOT_SELECTED") ); ?>';
  joms.language.event.start_date_not_selected = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_START_DATE_NOT_SELECTED") ); ?>';
  joms.language.event.end_date_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_END_DATE_NOT_SELECTED") ); ?>';
  joms.language.event.start_time_not_selected = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_START_TIME_NOT_SELECTED") ); ?>';
  joms.language.event.end_time_not_selected   = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_END_TIME_NOT_SELECTED") ); ?>';
  joms.language.event.end_date_too_early      = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_END_DATE_TOO_EARLY") ); ?>';
  joms.language.event.private                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_PRIVATE_EVENT") ); ?>';
  joms.language.event.private_tips            = '<?php echo addslashes( JText::_("COM_COMMUNITY_EVENTS_TYPE_TIPS") ); ?>';

  joms.language.custom || (joms.language.custom = {});
  joms.language.custom.predefined_button      = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_BUTTON") ); ?>';
  joms.language.custom.predefined_label       = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_LABEL") ); ?>';
  joms.language.custom.custom_button          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_BUTTON") ); ?>';
  joms.language.custom.custom_label           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_LABEL") ); ?>';

  joms.language.geolocation || (joms.language.geolocation = {});
  joms.language.geolocation.loading           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADING") ); ?>';
  joms.language.geolocation.loaded            = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADED") ); ?>';
  joms.language.geolocation.error             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_ERROR") ); ?>';
  joms.language.geolocation.select_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_SELECT_BUTTON") ); ?>';
  joms.language.geolocation.remove_button     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_REMOVE_BUTTON") ); ?>';
  joms.language.geolocation.near_here         = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_NEAR_HERE") ); ?>';
  joms.language.geolocation.empty             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_EMPTY") ); ?>';

  joms.language.fetch || (joms.language.fetch = {});
  joms.language.fetch['title_hint']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_FETCH_TITLE_HINT") ); ?>';
  joms.language.fetch['description_hint']     = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_FETCH_DESCRIPTION_HINT") ); ?>';

  joms.language.privacy || (joms.language.privacy = {});
  joms.language.privacy['public']             = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_PUBLIC") ); ?>';
  joms.language.privacy['public_desc']        = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_PUBLIC_DESC") ); ?>';
  joms.language.privacy['site_members']       = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS") ); ?>';
  joms.language.privacy['site_members_desc']  = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS_DESC") ); ?>';
  joms.language.privacy['friends']            = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_FRIENDS") ); ?>';
  joms.language.privacy['friends_desc']       = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_FRIENDS_DESC") ); ?>';
  joms.language.privacy['me']                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_ME") ); ?>';
  joms.language.privacy['me_desc']            = '<?php echo addslashes( JText::_("COM_COMMUNITY_PRIVACY_ME_DESC") ); ?>';

  joms.language.stream || (joms.language.stream = {});
  joms.language.stream.remove_comment         = '<?php echo addslashes( JText::_("COM_COMMUNITY_COMMENT_REMOVE") ); ?>';
  joms.language.stream.remove_comment_message = '<?php echo addslashes( JText::_("COM_COMMUNITY_COMMENT_REMOVE_MESSAGE") ); ?>';

  joms.language.datepicker || (joms.language.datepicker = {});
  joms.language.datepicker.sunday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_1") ); ?>';
  joms.language.datepicker.monday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_2") ); ?>';
  joms.language.datepicker.tuesday            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_3") ); ?>';
  joms.language.datepicker.wednesday          = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_4") ); ?>';
  joms.language.datepicker.thursday           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_5") ); ?>';
  joms.language.datepicker.friday             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_6") ); ?>';
  joms.language.datepicker.saturday           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_DAY_7") ); ?>';
  joms.language.datepicker.january            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1") ); ?>';
  joms.language.datepicker.february           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2") ); ?>';
  joms.language.datepicker.march              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3") ); ?>';
  joms.language.datepicker.april              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4") ); ?>';
  joms.language.datepicker.may                = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5") ); ?>';
  joms.language.datepicker.june               = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6") ); ?>';
  joms.language.datepicker.july               = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7") ); ?>';
  joms.language.datepicker.august             = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8") ); ?>';
  joms.language.datepicker.september          = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9") ); ?>';
  joms.language.datepicker.october            = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10") ); ?>';
  joms.language.datepicker.november           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11") ); ?>';
  joms.language.datepicker.december           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12") ); ?>';
  joms.language.datepicker.today              = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_CURRENT") ); ?>';
  joms.language.datepicker['clear']           = '<?php echo addslashes( JText::_("COM_COMMUNITY_DATEPICKER_CLEAR") ); ?>';

  joms.language.poll || (joms.language.poll = {});
  joms.language.poll.title_hint               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_HINT_TITLE")) ?>';
  joms.language.poll.add_option               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION")) ?>';
  joms.language.poll.hint_add_option          = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_HINT_ADD_OPTION")) ?>';
  joms.language.poll.expired_date             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_EXPIRED_DATE")) ?>';
  joms.language.poll.expired_time             = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_EXPIRED_TIME")) ?>';
  joms.language.poll.expired_in               = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_EXPIRED_IN")) ?>';
  joms.language.poll.expired_date_hint        = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_HINT_EXPIRED_DATE")) ?>';
  joms.language.poll.expired_time_hint        = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_HINT_EXPIRED_TIME")) ?>';
  joms.language.poll.allow_multiple_choices   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POLLS_MULTIPLE")) ?>';
  joms.language.poll.no_title                 = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_EMPTY_TITLE_WARNING")) ?>';
  joms.language.poll.not_enough_option        = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_NOT_ENOUGH_OPTION_WARNING")) ?>';
  joms.language.poll.no_category              = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_NO_CATEGORY_WARNING")) ?>';
  joms.language.poll.no_expiry_time           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_NO_EXPIRY_TIME_WARNING")) ?>';
  joms.language.poll.no_expiry_date           = '<?php echo addslashes( JText::_("COM_COMMUNITY_POSTBOX_POLL_NO_EXPIRY_DATE_WARNING")) ?>';
  joms.language.poll.create_limit_exceeded   = '<?php echo addslashes( JText::_("COM_COMMUNITY_POLLS_LIMIT") ); ?>';

</script>
<!-- dummy comment -->
<div class="joms-postbox" style="display:none;"></div>
