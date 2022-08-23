<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die();

$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

$my = CFactory::getUser();
?>
<div class="js-polls-module">
    <?php if(!empty($polls)): ?>
        <?php foreach ($polls as $poll): ?>
        <div class="joms-list__content_polls">
            <div class="joms-list__title_polls clearfix">
                <span class="icon-bar-chart"></span><h4><?php echo htmlspecialchars($poll->title); ?></h4>
            </div>
            <div class="joms-attachment-list joms-poll__container joms-poll__module-container-<?php echo $poll->id ?>">
                <?php require( JModuleHelper::getLayoutPath('mod_community_polls', 'default_item') ); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="joms-blankslate"><?php echo JText::_('COM_COMMUNITY_POLLS_NOITEM'); ?></div>
    <?php endif; ?>

    <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls'); ?>" class="joms-button--link" ><small><?php echo JText::_('COM_COMMUNITY_POLLS_VIEW_ALL'); ?></small></a>
</div>