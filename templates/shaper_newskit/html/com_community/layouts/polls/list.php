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

$config	= CFactory::getConfig();
?>


<?php if ($polls) { ?>

<ul class="joms-list--card joms-poll__list">
    <?php

    $my = CFactory::getUser();
    $pollModel = CFactory::getModel('polls');

    for ($i = 0; $i < count($polls); $i++) {
        $poll =& $polls[$i];

        $poll->expired = $poll->isExpired();
        $expired_class = $poll->expired ? 'joms-poll__expired' : '';

        $isMine = $my->id == $poll->creator;
        $creator = CFactory::getUser($poll->creator);

        $canEdit = false;
        $canDelete = false;

        if ($isMine || $my->authorise('community.edit', 'polls.' . $poll->id, $poll)) {
            $canEdit = true;
        }

        if ($isMine || $my->authorise('community.delete', 'polls.' . $poll->id, $poll)) {
            $canDelete = true;
        }

        $pollItems = $pollModel->getPollItems($poll->id);

        // get total votes
        $totalVotes = 0;
        foreach ($pollItems as $item) {
            $totalVotes = $totalVotes + $item->count;
        }
    ?>

    <li class="joms-list__item joms-poll__item joms-poll__item-<?php echo $poll->id; ?>">
        <div class="joms-poll__header">
            <div class="joms-avatar--poll <?php echo CUserHelper::onlineIndicator($creator); ?>">
                <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id);?>"><img src="<?php echo $creator->getAvatar();?>" alt="avatar" data-author="<?php echo $creator->id; ?>" ></a>
            </div>
            <div class="joms-poll__meta">
                <?php echo JText::_('COM_COMMUNITY_POLLS_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($creator->id);?>"><?php echo $creator->getDisplayName(); ?></a>

                <?php
                    if($poll->groupid){
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($poll->groupid);
                        echo JText::sprintf('COM_COMMUNITY_POLLS_FROM_GROUP','<a href="' . CUrlHelper::groupLink($group->id) . '">' . $group->name. '</a>');
                    } else if($poll->eventid){
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($poll->eventid);
                        echo JText::sprintf('COM_COMMUNITY_VIDEOS_FROM_EVENT','<a href="' . CUrlHelper::eventLink($event->id) . '">' . $event->title. '</a>');
                    }
                ?>

            </div>
            <div class="joms-gap">&nbsp;</div>
            <div class="joms-block">
                <?php if ($poll->permissions == PRIVACY_PUBLIC) { ?>
                    <span class="joms-list__permission">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-earth"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_PRIVACY_PUBLIC'); ?>
                    </span>
                <?php } else if ($poll->permissions == PRIVACY_MEMBERS) { ?>
                    <span class="joms-list__permission">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_PRIVACY_SITE_MEMBERS'); ?>
                    </span>
                <?php } else if ($poll->permissions == PRIVACY_FRIENDS) { ?>
                    <span class="joms-list__permission">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS'); ?>
                    </span>
                <?php } else if ($poll->permissions == PRIVACY_PRIVATE) { ?>
                    <span class="joms-list__permission">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_PRIVACY_ME'); ?>
                    </span>
                <?php } ?>
            </div>
        </div>

        <?php if ($canEdit || $canDelete) { ?>
            <div class="joms-padding">
                <div class="joms-focus__button--options--desktop polls">
                    <a class="joms-button--options" data-ui-object="joms-dropdown-button" href="javascript:">
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                        </svg>
                    </a>
                    <ul class="joms-dropdown">
                        <?php if ($canEdit) { ?>
                            <li><a href="<?php
                                    if ($poll->groupid) {
                                        echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id . '&groupid=' . $poll->groupid);
                                    } else if ($poll->eventid) {
                                        echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id . '&eventid=' . $poll->eventid);
                                    } else {
                                        echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id);
                                    }
                                ?>"><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></a></li>
                        <?php } ?>
                        <?php if ($canDelete) { ?>
                            <li><a href="javascript:" onclick="joms.view.poll.delete('<?php echo $poll->id; ?>');"><?php echo JText::_('COM_COMMUNITY_DELETE'); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>

                <a class="joms-focus__button--options" data-ui-object="joms-dropdown-button"><?php echo JText::_('COM_COMMUNITY_OPTIONS'); ?></a>
                <ul class="joms-dropdown">
                    <?php if ($canEdit) { ?>
                        <li><a href="<?php
                                if ($poll->groupid) {
                                    echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id . '&groupid=' . $poll->groupid);
                                } else if ($poll->eventid) {
                                    echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id . '&eventid=' . $poll->eventid);
                                } else {
                                    echo CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $poll->id);
                                }
                            ?>"><?php echo JText::_('COM_COMMUNITY_EDIT'); ?></a></li>
                    <?php } ?>
                    <?php if ($canDelete) { ?>
                        <li><a href="javascript:" onclick="joms.view.poll.delete('<?php echo $poll->id; ?>');"><?php echo JText::_('COM_COMMUNITY_DELETE'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <div class="joms-list__content_polls">

            <div class="joms-list__title_polls clearfix">
                <span class="icon-bar-chart"></span><h4><?php echo $this->escape($poll->title); ?></h4>
            </div>

            <div class="joms-attachment-list joms-poll__container joms-poll__container-<?php echo $poll->id ?> <?php echo $expired_class ?>" >
                <?php $this->set('poll', $poll)->load('stream/poll-container'); ?>
            </div>

            <ul class="joms-poll-info">
              <li>
                <svg class="joms-icon" viewBox="0 0 16 16"><use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use></svg>

                <?php
                    if ($poll->isExpired()) {
                        echo JText::_('COM_COMMUNITY_POLL_IS_ENDED') .' '.$poll->getEndDateHTML();
                    } else {
                        echo JText::_('COM_COMMUNITY_POLL_WILL_BE_ENDED_ON') .' '.$poll->getEndDateHTML();
                    }
                ?>
              </li>
              <li>
                <span class="fa fa-folder"></span>
                <strong><?php echo JText::_('COM_COMMUNITY_POLLS_CATEGORY'); ?>: </strong><?php echo JText::_($poll->getCategoryName()); ?>
              </li>
            </ul>
        </div>
    </li>

    <?php } ?>
</ul>

<?php } else { ?>
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_POLLS_NOITEM'); ?></div>
<?php } ?>

<?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
