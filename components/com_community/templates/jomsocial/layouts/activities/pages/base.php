<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

$user = CFactory::getUser($this->act->actor);

// Setup event table
$page = JTable::getInstance('Page', 'CTable');
$page->load($this->act->pageid);
$this->set('page', $page);

if(!empty($this->act->params)){
     if (!is_object($this->act->params)) {
        $act->params = new JRegistry($this->act->params);
    }
	// Load params
	$action = $this->act->params->get('action');
	$actors = $this->act->params->get('actors');
}
else {
	$action='';
	$actors='';
}
$this->set('actors', $actors);
$my = CFactory::getUser();
$this->load('activities.stream.options');
?>

	<?php if( $this->act->app == 'pages.wall') { ?>
		<?php $this->load('activities.profile'); ?>
	<?php } else if( $this->act->app == 'pages.featured') { ?>
		<?php $this->load('activities/pages/featured'); ?>
	<?php } else if( $action == 'page.create') { ?>
		<?php $this->load('activities/pages/create'); ?>
	<?php } else if( $action == 'page.update') { ?>
		<?php $this->load('activities.pages.update'); ?>
	<?php } else if( $action == 'page.join') { ?>
		<?php $this->load('activities.pages.join'); ?>
	<?php } else if( $action == 'page.discussion.create') { ?>
		<?php $this->load('activities.pages.discussion.create'); ?>
	<?php } else if( $action == 'page.discussion.reply') { ?>
		<?php $this->load('activities.pages.discussion.reply'); ?>
    <?php } else if( $this->act->app == 'discussion.like') { ?>
        <?php $this->load('activities.pages.discussion.like'); ?>
	<?php } else if( $this->act->app == 'pages.bulletin') { ?>
		<?php $this->load('activities.pages.bulletin'); ?>
	<?php } else { ?>
	<?php
		$table = JTable::getInstance('Activity','CTable');
		$table->load($this->act->id);
		if(!$table->delete()){
	?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
        <img src="<?php echo $user->getThumbAvatar(); ?>" data-author="<?php echo $user->id; ?>" alt="<?php echo $user->getDisplayName(); ?>" ></a>
    </div>
    <div class="joms-stream__meta">
        <?php
            $html = CPages::getActivityContentHTML($act);
            echo $html;
        ?>
    </div>
</div>

<?php $this->load('stream/footer'); ?>

<?php }} ?>
