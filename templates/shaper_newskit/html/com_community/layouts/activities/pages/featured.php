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
$user = CFactory::getUser($this->act->actor);
$page = JTable::getInstance('Page', 'CTable');
$page->load($this->act->cid);

$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::groupLink($page->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $page->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </a>
    </div>
    <div class="joms-stream__meta">
        <?php echo $this->act->title; ?>
        <span class="joms-stream__time"><small><?php echo $createdTime; ?></small></span>
    </div>
</div>

<div class="joms-stream__body">
    <div class="joms-media">
        <h4 class="joms-text--title"><a href="<?php echo $this->page->getLink();?>"> <?php echo $page->name; ?>
        </a></h4>
        <p><?php echo JHTML::_('string.truncate',strip_tags($page->description) , $config->getInt('streamcontentlength')); ?></p>
    </div>
</div>

<?php
    $this->act->isFeatured = true;
    $this->load('stream/footer');
?>
