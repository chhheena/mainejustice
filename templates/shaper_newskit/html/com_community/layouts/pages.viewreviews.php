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

$addButton = true;
if (!$isMember) {
    $addButton = false;
}

$canEdit = false;
if ($isMine || $isAdmin || CFactory::getUser()->authorise('community.pageedit', 'com_community')) { 
    $canEdit = true;
}
?>
<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $title; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php if ($addButton) { ?>
            <button onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=pages&task=createreview&pageid='.$page->id); ?>';" class="joms-button--add">
                <?php 
                    if (!$isRated) {
                        echo JText::_('COM_COMMUNITY_PAGES_CREATE_REVIEW');
                    } else {
                        echo JText::_('COM_COMMUNITY_PAGES_EDIT_YOUR_REVIEW');
                    }
                ?>
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                </svg>
            </button>
            <?php } ?>
        </div>
    </div>
    <?php if ($reviews) : ?>
        <?php
            foreach ($reviews as $row) {
                $user = CFactory::getUser($row->userid);
        ?>
        <div class="joms-stream__container joms-stream--discussion">
            <div class="joms-stream__header">
                <div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                    <a href="<?php echo CContentHelper::injectTags('{url}',$row->params,true); ?>">
                        <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>" />
                    </a>
                </div>
                <div class="joms-stream__meta">
                    <?php if($user->id > 0) :?>
                        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" data-joms-username class="joms-stream__user active"><?php echo $user->getDisplayName(); ?></a>
                    <?php else :
                        echo $user->getDisplayName();
                    endif;
                    ?>
                    <div class="joms-stream__time">
                        <small><?php echo CTimeHelper::timeLapse(CTimeHelper::getDate($row->created)); ?></small>
                    </div>
                </div>
            </div>
            <div class="joms-stream__body">
                <div class="cStream-Content">
                    <?php echo CStringHelper::ratingStar($row->rating); ?>
                    <p><strong><?php echo $row->title; ?></strong></p>
                    <p><?php echo $row->review; ?></p>
                </div>
            </div>

            <?php if ($canEdit) { ?>
                <p class="clearfix">
                    <button onclick="if (confirm('<?php echo JText::sprintf('COM_COMMUNITY_POLLS_DELETE_MESSAGE', $row->title) ?>')) window.location='<?php echo CRoute::_('index.php?option=com_community&view=pages&task=deletereview&reviewid='.$row->reviewid.'&pageid='.$page->id.'&userid='.$row->userid); ?>';" class="joms-button--neutral joms-button--full-small">
                        <?php echo JText::_('COM_COMMUNITY_DELETE') ?>
                    </button>
                    &nbsp;&nbsp;&nbsp;
                    <button onclick="window.location='<?php echo CRoute::_('index.php?option=com_community&view=pages&task=editreview&reviewid='.$row->reviewid.'&pageid='.$page->id.'&userid='.$row->userid); ?>';" class="joms-button--neutral joms-button--full-small">
                        <?php echo JText::_('COM_COMMUNITY_EDIT') ?>
                    </button>
                </p>
            <?php } ?>

        </div>
        <?php } ?>
    <?php endif; ?>

<?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
</div>