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

$activity = new CActivity($act);
$permission = $activity->getPermission($my->id);

$canEdit = false;
$canDelete = false;
$isMine = $my->id == $poll->creator;

if ($isMine || $my->authorise('community.edit', 'polls.' . $poll->id, $poll)) {
    $canEdit = true;
}

if ($isMine || $my->authorise('community.delete', 'polls.' . $poll->id, $poll)) {
    $canDelete = true;
}

$pollSetting = false;
if ($canEdit || $canDelete) {
    $pollSetting = true;
}

// get stream actor for like
if (strpos($act->app, 'like')) {
    if (!$act->actor) {
        $params = $act->params;
        $actors = $params->get('actors');
        if ( !is_array($actors) ) {
            $actors = array_reverse( explode(',', $actors) );
        }
        
        if (isset($actors[0]) && $actors[0]) {
            $act->actor = $actors[0];
        }
    }
}
?>

<div class="joms-list__options">

    <?php if ($permission->showButton || ($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) ) { ?>
    <a href="javascript:" class="joms-button--options" data-ui-object="joms-dropdown-button">
        <svg viewBox="0 0 16 16" class="joms-icon">
            <use xlink:href="<?php echo JUri::getInstance(); ?>#joms-icon-arrow-down"></use>
        </svg>
    </a>
    <?php } ?>

    <ul class="joms-dropdown">
        <?php if (CFactory::getConfig()->get('enablereporting') && (($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) || ($my->id > 0 && $my->id != $act->actor)) && !COwnerHelper::isCommunityAdmin() ) { ?>
            <li>
                <a href="javascript:" data-propagate="1" onclick="joms.api.streamReport('<?php echo $act->id; ?>', '0');">
                    <?php echo JText::_('COM_COMMUNITY_REPORT'); ?>
                </a>
            </li>
            <li class="separator"></li>
        <?php } ?>


        <?php if ($permission->featureActivity) { ?>
            <li>
                <a href="javascript:" data-propagate="1" onclick="joms.api.streamAddFeatured('<?php echo $act->id; ?>');">
                    <?php echo JText::_('COM_COMMUNITY_STREAM_ACTIVITY_FEATURE'); ?>
                </a>
            </li>
        <?php } else if($permission->unfeatureActivity) { ?>
            <li>
                <a href="javascript:" data-propagate="1" onclick="joms.api.streamRemoveFeatured('<?php echo $act->id; ?>');">
                    <?php echo JText::_('COM_COMMUNITY_STREAM_ACTIVITY_UNFEATURE'); ?>
                </a>
            </li>
        <?php } ?>

        <?php if ($pollSetting) { ?>
            <li>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pollId=' . $poll->id) ?>" data-propagate="1">
                    <?php echo JText::_('COM_COMMUNITY_ACTIVITY_VIEW_POLL'); ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
