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

/**
 * @since 3.2 we'll use CActivity for each activity object
 * @todo in sprint 3 we must move everything into CActivity while passing into template layout
 */
/* Temporary fix for sprint 2 */

$my = CFactory::getUser();
$pollModel = CFactory::getModel('polls');
$pollItems = $pollModel->getPollItems($poll->id);

$counts = array_map( function($item) {
    return $item->count;
}, $pollItems);

$poll->collapsed = !empty($poll->collapsed) ? $poll->collapsed : 0;

$max = max($counts);

// only group and event member can vote
if ($poll->groupid) {
    $groupModel = CFactory::getModel('groups');
    $isMember = $groupModel->isMember($my->id, $poll->groupid);

    if (!$poll->expired && !$isMember && !COwnerHelper::isCommunityAdmin($my->id)) {
        $poll->expired = true;
    }
} else if ($poll->eventid) {
    $event = JTable::getInstance('Event', 'CTable');
    $event->load($poll->eventid);
    $isMember = $event->isMember($my->id);

    if (!$poll->expired && !$isMember && !COwnerHelper::isCommunityAdmin($my->id)) {
        $poll->expired = true;
    }
}
?>

<div class="joms-poll__loader">
    <img src="<?php echo JUri::root() . 'components/com_community/assets/ajax-loader.gif' ?>" alt="loader" >
</div>
<ul class="joms-poll__option-list joms-poll__option-list-<?php echo $poll->id ?>" data-collapse="<?php echo $poll->collapsed ?>">
    <?php foreach ($pollItems as $key => $item): ?>
    <li <?php echo ($key > 4 && !$poll->collapsed) ? 'style="display:none"': '' ?>>
        <?php $checked = ($pollModel->asPollItemVoter($poll->id, $item->id, $my->id) ? 'checked="checked"' : ''); ?>
        <div class="joms-poll__input-container">
            <div class="joms-poll__checkbox--custom <?php echo $poll->multiple ? 'input--checkbox':'input--radio' ?>">
                <input 
                    class="joms-poll_input joms-poll_input-<?php echo $item->id ?>" 
                    name="poll<?php echo $poll->id ?>"
                    id="joms-poll__stream-input<?php echo $item->id ?>"
                    type="checkbox"
                    <?php echo ($poll->expired || !$my->id) ? 'disabled': "onclick=\"joms.view.poll.vote($poll->id , $item->id )\"" ?>
                    <?php echo $checked ?> />
                <label for="joms-poll__stream-input<?php echo $item->id ?>"></label>
            </div>
            <label for="joms-poll__stream-input<?php echo $item->id ?>" class="joms-poll__option joms-poll__option-<?php echo $item->id ?>">
                <?php echo htmlspecialchars($item->value); ?>
            </label>
        </div>
        <div class="joms-poll__progress">
            <?php
                if ($max > 0) {
                    $progress = round(($item->count / $max) * 100 );
                } else {
                    $progress = 0;
                }
            ?>
            <div class="joms-poll__progress-bar" style="width: <?php echo $progress . '%' ?>;"></div>
        </div>
        <div class="joms-poll__voted-users">
            <?php if($item->count): ?>
            <a href="javascript:;" onclick="joms.view.poll.showVotedUsers(<?php echo $poll->id ?>, <?php echo $item->id ?>)"><?php echo JText::sprintf('COM_COMMUNITY_POLLS_PEOPLE_VOTE', $item->count); ?></a>
            <?php else: ?>
            <a href="javascript:;"><?php echo JText::sprintf('COM_COMMUNITY_POLLS_PEOPLE_VOTE', $item->count); ?></a>
            <?php endif; ?>
        </div>
    </li>
    <?php endforeach; ?>
</ul>

<?php if (count($pollItems) > 5 && !$poll->collapsed): ?>
<div class="joms-poll__more joms-poll__more-<?php echo $poll->id ?>">
    <div class="joms-poll__more-inner">
        <a href="javascript:;" onclick="joms.view.poll.moreOptions(<?php echo $poll->id ?>)"><?php echo count($pollItems) - 5 ?> <?php echo JText::_('COM_COMMUNITY_POSTBOX_POLL_MORE_OPTION'); ?></a>
    </div>
</div>
<?php endif ?>