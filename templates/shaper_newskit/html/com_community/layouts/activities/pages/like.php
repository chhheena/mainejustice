<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

$params = $act->params;
$users = $params->get('actors');

if (!is_array($users)) {
    $users = array_reverse(explode(',', $users));
}

$truncateVal = 60;
$user = CFactory::getUser($users[0]);
$date = JDate::getInstance($act->created);

if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

// Setup page table
$page = JTable::getInstance('Page', 'CTable');
$page->load($act->pageid);
$this->set('page', $page);
?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <?php if (count($users) > 1 && false) { // added false for now because we have to show the last user avatar ?>
            <svg class="joms-icon" viewBox="0 0 16 16">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
            </svg>
        <?php } else { ?>
            <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
            </a>
        <?php } ?>
    </div>
    <div class="joms-stream__meta">
        <?php echo CLikesHelper::generateHTML($act, $likedContent) ?>
        <span class="joms-stream__time"><small><?php echo $createdTime; ?></small></span>
    </div>
    <?php
        $my = CFactory::getUser();
        $this->load('activities.stream.options');
    ?>
</div>

<div class="joms-stream__body">
    <div class="joms-media like">
        <a href="<?php echo $this->page->getLink();?>">
            <div class="joms-media__cover">
                <img src="<?php echo $this->page->getCover(); ?>" alt="<?php echo $this->page->name; ?>" />
            </div>
        </a>
        <h4 class="joms-text--title">
            <a href="<?php echo $this->page->getLink();?>">
                <?php echo JHTML::_('string.truncate',$this->page->name , $truncateVal); ?>
            </a>
        </h4>
        <p class="joms-text--desc"><?php echo JHTML::_('string.truncate',strip_tags($page->description) , $config->getInt('streamcontentlength')); ?></p>
    </div>
</div>
