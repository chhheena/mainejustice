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

$item_per_page = 8;
$count = count( $users );
$pages = ceil( $count / $item_per_page );

?>

<div style="max-height:250px; overflow:auto; padding: 10px;">

<?php
$my = CFactory::getUser();

for ($i = 0; $i < $count; $i++) {
    $user = $users[$i];
    $isMine = ($my->id == $user->id);
    $isFriend = $my->isFriendWith($user->id);
    $isBlocked = $user->isBlocked();
    $isWaitingApproval = CFriendsHelper::isWaitingApproval($my->id, $user->id);
    $isWaitingResponse = CFriendsHelper::isWaitingApproval($user->id, $my->id);
?>

<div>
    <div style="padding: 2px 0;" class="joms-stream__header">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </div>
        <div class="joms-stream__meta">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
            <?php if ( !$isMine ) { ?>
                <!-- Friending buton -->
                <?php if ( $isFriend ) { ?>
                    <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendRemove('<?php echo $user->id;?>')">
                        <?php echo JText::_('COM_COMMUNITY_FRIENDS_REMOVE'); ?>
                    </a>
                <?php } else if (!$isBlocked) { ?>
                    <?php if ($isWaitingApproval) { ?>
                        <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendAdd('<?php echo $user->id;?>')">
                            <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                        </a>
                    <?php } else if ($isWaitingResponse) { ?>
                        <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendResponse('<?php echo $user->id;?>')">
                            <?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?>
                        </a>
                    <?php } else if (CFactory::getUser()->authorise('community.request', 'friends.' . $user->id)) { ?>
                        <a href="javascript:" class="joms-button--neutral joms-button--small" onclick="joms.api.friendAdd('<?php echo $user->id;?>')">
                            <?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
                        </a>
                    <?php } ?>
                <?php } ?>
            <?php } ?>

            <span class="joms-stream__time"> </span>
        </div>
    </div>
</div>

<?php } ?>

</div>
