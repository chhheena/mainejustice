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

<div class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><?php echo $original->item_title; ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <?php echo $story->item_description; ?>
                <?php if ($config['show_enclosures']): ?>
                    <?php echo JHtmlRssFactoryFeeds::getEnclosuresFromStory($story); ?>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="<?php echo $story->item_link; ?>" type="button" class="btn btn-primary">
                    <?php echo FactoryTextRss::_('feed_story_display_read_more'); ?>
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php echo FactoryTextRss::_('feed_story_display_modal_close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
