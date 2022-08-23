<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

extract($displayData)

?>

<div class="modal hide fade">
    <div class="modal-header">
        <h3><?php echo $original->item_title; ?></h3>
    </div>

    <div class="modal-body">
        <?php echo $story->item_description; ?>
        <?php if ($config['show_enclosures']): ?>
            <?php echo JHtmlRssFactoryFeeds::getEnclosuresFromStory($story); ?>
        <?php endif; ?>
    </div>

    <div class="modal-footer">
        <a href="<?php echo $story->item_link; ?>" class="btn btn-primary <?php echo($modal ? 'modal-mootools' : ''); ?>" <?php echo $target; ?> <?php echo $modal; ?>>
            <?php echo FactoryTextRss::_('feed_story_display_read_more'); ?>
        </a>
        <button class="btn" type="button" data-dismiss="modal">
            <?php echo FactoryTextRss::_('feed_story_display_modal_close'); ?>
        </button>
    </div>
</div>
