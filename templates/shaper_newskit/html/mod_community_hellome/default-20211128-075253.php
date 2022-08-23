<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

use Joomla\Utilities\ArrayHelper;

defined( '_JEXEC' ) or die( 'Unauthorized Access' );

$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

?>



<div class="joms-module">

<?php if($user->id){

$userParams = $user->getParams();
$config = CFactory::getConfig();
$my = CFactory::getUser();
$url = CRoute::_('index.php?option=com_community');
// $isMine = COwnerHelper::isMine($my->id, $user->id);
// $isFriend = CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id;
// $isWaitingApproval = CFriendsHelper::isWaitingApproval($my->id, $user->id);
// $isWaitingResponse = CFriendsHelper::isWaitingApproval($user->id, $my->id);
// $isBlocked = $user->isBlocked();

//links information
$photoEnabled = ($config->get('enablephotos')) ? true : false;
$eventEnabled = ($config->get('enableevents')) ? true : false;
$groupEnabled = ($config->get('enablegroups')) ? true : false;
$videoEnabled = ($config->get('enablevideos')) ? true : false;
$pollEnabled = ($config->get('enablepolls')) ? true : false;
$pageEnabled = ($config->get('enablepages')) ? true : false;
$followerEnabled = ($config->get('enablefollowers')) ? true : false;


//likes
// CFactory::load('libraries', 'like');
// $like = new Clike();
// $isLikeEnabled = $like->enabled('profile') && $userParams->get('profileLikes', 1) ? 1 : 0;
// $isUserLiked = $like->userLiked('profile', $user->id, $my->id);
// /* likes count */
// $likes = $like->getLikeCount('profile', $user->id);

$profileFields = '';
$themeModel = CFactory::getModel('theme');
$profileModel = CFactory::getModel('profile');
$settings = $themeModel->getSettings('profile');

$profile = $profileModel->getViewableProfile($user->id, $user->getProfileType());
$profile = ArrayHelper::toObject($profile);

$groupmodel = CFactory::getModel('groups');
$profile->_groups = $groupmodel->getGroupsCount($profile->id);

$eventmodel = CFactory::getModel('events');
$profile->_events = $eventmodel->getEventsCount($profile->id);

$profile->_friends = $user->_friendcount;

$videoModel = CFactory::getModel('Videos');
$profile->_videos = $videoModel->getVideosCount($profile->id);

$photosModel = CFactory::getModel('photos');
$profile->_photos = $photosModel->getPhotosCount($profile->id);

$pollsModel = CFactory::getModel('Polls');
$profile->_polls = $pollsModel->getPollsCount($profile->id);

$follower = JTable::getInstance('Follower', 'CTable');
$profile->_follower = $follower->getFollowerCount($profile->id);
$profile->_following = $follower->getFollowingCount($profile->id);

$pagemodel = CFactory::getModel('pages');
$profile->_pages = $pagemodel->getPagesCreationCount($profile->id);
?>

<div class="joms-module--hellome">

    <div class="joms-hcard">

        <div class="joms-hcard__cover" >

            <img src="<?php echo $user->getCover(); ?>" alt="<?php echo $user->getDisplayName(); ?>" style="width:100%;top:<?php echo $userParams->get('coverPosition', ''); ?>">

            <?php if($moduleParams->get('show_avatar') || $moduleParams->get('show_name')) { ?>

                <div class="joms-hcard__info">
                    <?php if($moduleParams->get('show_avatar')){ ?>
                    <div class="joms-avatar">
                        <a href="<?php echo CUrlHelper::userLink($user->id); ?>"><img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>"></a>
                    </div>
                    <?php } ?>
                    <?php if($moduleParams->get('show_name')){ ?>
                    <div class="joms-hcard__info-content">
                        <h3 class="reset-gap"><?php echo $user->getDisplayName(false, true); ?></h3>
                        <div class="joms-gap--small"></div>
                    </div>
                    <?php } ?>
                </div>

           <?php } ?>

        </div>
    </div>

    <?php if($moduleParams->get('show_badge')) { ?>
    <div class="joms-hcard__badges">
        <img src="<?php echo $badge->current->image;?>" alt="<?php echo $badge->current->title;?>" />
    </div>
    <?php } ?>

    <?php if($moduleParams->get('show_menu')) { ?>
        <ul class="joms-list joms-list--hellome">
            <li><?php echo JText::_('MOD_HELLOME_MY_FRIENDS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends'); ?>"><?php echo $user->_friendcount; ?></a></span></li>
            
            <?php if($followerEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_FOLLOWERS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=followers&userid='.$profile->id); ?>"><?php echo $totalFollowers; ?></a></span></li>
                <li><?php echo JText::_('MOD_HELLOME_MY_FOLLOWING'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=followers&task=following&userid='.$profile->id); ?>"><?php echo $totalFollowing; ?></a></span></li>
            <?php } ?>

            <?php if($photoEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_PHOTOS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos'); ?>"><?php echo $totalPhotos; ?></a></span></li>
            <?php } ?>

            <?php if($videoEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_VIDEOS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos'); ?>"><?php echo $totalVideos; ?></a></span></li>
            <?php } ?>

            <?php if($groupEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_GROUPS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups'); ?>"><?php echo $totalGroups; ?></a></span></li>
            <?php } ?>

            <?php if($eventEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_EVENTS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=myevents'); ?>"><?php echo $totalEvents; ?></a></span></li>
            <?php } ?>

            <?php if($pageEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_PAGES'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=mypages'); ?>"><?php echo $totalPages; ?></a></span></li>
            <?php } ?>
            <?php if($pollEnabled) { ?>
                <li><?php echo JText::_('MOD_HELLOME_MY_POLLS'); ?><span><a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&task=mypolls'); ?>"><?php echo $totalPolls; ?></a></span></li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="joms-action--hellome">

    <?php if($moduleParams->get('show_notifications')){ ?>

        <div>
            <a class="joms-button--hellome" title="<?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_GLOBAL'); ?>"
                    href="javascript:"
                    onclick="joms.popup.notification.global();">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-earth"></use>
                </svg>
                <span><small class="joms-js--notiflabel-general"><?php echo ($newEventInviteCount) ? $newEventInviteCount : ''; ?></small></span>
            </a>
        </div>
        <div>
            <a class="joms-button--hellome" title="<?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_INVITE_FRIENDS'); ?>"
                    href="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=pending'); ?>"
                    onclick="joms.popup.notification.friend(); return false;">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                </svg>
                <span><small class="joms-js--notiflabel-frequest"><?php echo ($newFriendInviteCount) ? $newFriendInviteCount : ''; ?></small></span>
            </a>
        </div>
        <div>
            <a class="joms-button--hellome" title="<?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_INBOX'); ?>"
                    href="<?php echo CRoute::_('index.php?option=com_community&view=chat'); ?>"
                    onclick="joms.popup.notification.chat(this); return false;">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-envelope"></use>
                </svg>
                <span><small class="joms-js--notiflabel-chat"><?php echo ($newChatCount) ? $newChatCount : ''; ?></small></span>
            </a>
            <ul class="joms-popover joms-arrow--top joms-popover--toolbar-chat">
                <li class="joms-js--empty" style="display:block">
                    <span><?php echo JText::_('COM_COMMUNITY_CHAT_NOTIF_NO_NEW_MESSAGE') ?></span>
                </li>
                <div>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=chat'); ?>" class="joms-button--neutral joms-button--full"><?php echo JText::_('COM_COMMUNITY_CHAT_NOTIF_SHOW_ALL') ?></a>
                </div>
            </ul>
        </div>

    <?php } ?>

        <?php if($params->get('show_logout',1)){ ?>
        <div>
            <a href="javascript:void(0);" onclick="document.getElementById('js-hellome-logout-form').submit();" class="joms-button--hellome logout">
                <svg viewBox="0 0 16 16" class="joms-icon joms-icon--white">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-switch"></use>
                </svg>
            </a>
        </div>
        <form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="js-hellome-logout-form" style="display: none;">
            <input type="hidden" name="option" value="com_users" />
            <input type="hidden" name="task" value="user.logout" />
            <input type="hidden" name="return" value="<?php echo isset($logoutLink) ? $logoutLink : ''; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
        <?php } ?>
    </div>

</div>

<?php }else{

$config = CFactory::getConfig();
$document = JFactory::getDocument();
$usersConfig = JComponentHelper::getParams('com_users');
$fbHtml = '';

if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
    $facebook = new CFacebook();
    $fbHtml = $facebook->getLoginHTML();
}

$twitterHtml = '';

/* Twitter login */
if ($config->get('twitterconnectkey') && $config->get('twitterconnectsecret') && !$config->get('usejfbc')) {
    $twitter = new CTwitter();
    $twitterHtml = $twitter->getLoginHTML();
}

$linkedinHtml = '';

/* LinkedIn login */
if ($config->get('linkedinclientid') && $config->get('linkedinsecret') && !$config->get('usejfbc')) {
    $linkedin = new CLinkedin();
    $linkedinHtml = $linkedin->getLoginHTML();
}

$googleHtml = '';

/* Google login */
if ($config->get('googleclientid') && !$config->get('usejfbc')) {
    $google = new CGoogle();
    $googleHtml = $google->getLoginHTML('hellome');

    $document->addCustomTag('<script src="https://apis.google.com/js/api:client.js"></script>');
}

if ($config->get('usejfbc')) {
    if (class_exists('JFBCFactory')) {
       $providers = JFBCFactory::getAllProviders();
       $fbHtml = '';
       foreach($providers as $p){
            $fbHtml .= $p->loginButton();
       }
    }
}

?>

<form class="joms-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" >
    <div class="joms-input--append">
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
        </svg>
        <input type="text" name="username" class="joms-input" placeholder="<?php echo JText::_('MOD_HELLOME_USERNAME'); ?>">
    </div>
    <div class="joms-input--append">
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
        </svg>
        <input type="password" name="password" class="joms-input" placeholder="<?php echo JText::_('MOD_HELLOME_PASSWORD'); ?>">
    </div>

    <?php if(CSystemHelper::tfaEnabled()){?>
        <div class="joms-input--append">
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-key"></use>
            </svg>
            <input type="text" name="secretkey" class="joms-input" placeholder="<?php echo JText::_('COM_COMMUNITY_AUTHENTICATION_KEY'); ?>">
        </div>
    <?php } ?>
    <button class="joms-button--primary joms-button--small"><?php echo JText::_('MOD_HELLOME_LOGIN'); ?></button>

    <?php if ($usersConfig->get('allowUserRegistration')) : ?>
      <a class="joms-button--secondary joms-button--small" href="<?php echo CRoute::_('index.php?option=com_community&view=register', false); ?>">
        <?php echo JText::_('MOD_HELLOME_REGISTER'); ?>
      </a>
    <?php endif; ?>

    <?php if (!$usersConfig->get('allowUserRegistration') && $config->get('invite_only_request')) : ?>
      <a class="joms-button--secondary joms-button--small" href="<?php echo CRoute::_('index.php?option=com_community&view=registerinvite', false); ?>">
        <?php echo JText::_('MOD_HELLOME_REQUEST_INVITE'); ?>
      </a>
    <?php endif; ?>

    <input type="hidden" name="option" value="<?php echo COM_USER_NAME; ?>"/>
    <input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGIN; ?>"/>
    <input type="hidden" name="return" value="<?php echo $loginLink; ?>"/>
    <div class="joms-js--token"><?php echo JHTML::_('form.token'); ?></div>


    <?php if ( JPluginHelper::isEnabled('system', 'remember') && $moduleParams->get('remember_me') != 3) { ?>

        <div class="joms-checkbox" style="<?php if($moduleParams->get('remember_me') == 2){ echo 'visibility:hidden'; } ?>">
            <input type="checkbox" value="yes" name="remember" <?php if($moduleParams->get('remember_me') == 0 || $moduleParams->get('remember_me') == 2){ echo 'checked'; }?>>
            <span><?php echo JText::_('MOD_HELLOME_REMEMBER_ME'); ?></span>
        </div>
    <?php } ?>

    <div class="joms-gap"></div>

    <?php if($moduleParams->get('show_forgotusr')) { ?>
        <a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=remind'); ?>"><?php echo JText::_('MOD_HELLOME_FORGET_USERNAME'); ?></a>&nbsp;
    <?php } ?>

    <?php if($moduleParams->get('show_forgotpwd')) { ?>
        <a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=reset'); ?>"><?php echo JText::_('MOD_HELLOME_FORGET_PASSWORD'); ?></a>
    <?php } ?>

    <?php if($moduleParams->get('show_activation') && $usersConfig->get('useractivation')) { ?>
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=register&task=activation'); ?>"><?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION'); ?></a>
    <?php } ?>
    <p>
        <?php if($moduleParams->get('show_facebook')) {
            echo $fbHtml;
        } ?>
    </p>
    <p>
        <?php if($moduleParams->get('show_google')) {
            echo $googleHtml;
        } ?>
    </p>
    <p>
        <?php if($moduleParams->get('show_twitter')) {
            echo $twitterHtml;
        } ?>
    </p>
    <p>
        <?php if($moduleParams->get('show_linkedin')) {
            echo $linkedinHtml;
        } ?>
    </p>

</form>

<?php }?>

</div>
