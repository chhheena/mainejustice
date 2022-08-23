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

?>

<div style="display: flex; flex-direction: column; height: 100%;">
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; background-color: #333333; color: #eeeeee; padding: 15px 20px; border-bottom: 2px solid #000000;">
            <?php if ($this->voteEnabled): ?>
                <div style="flex-basis: 80px;">
                    <?php echo JHtml::_(
                        'RssFactoryFeeds.displayStoryVotes',
                        $this->story,
                        array(
                            'voting'       => true,
                            'votingArrows' => true,
                        )); ?>
                </div>
            <?php endif; ?>

            <div style="flex-grow: 1; max-width: 800px;">
                <?php if ($this->story): ?>
                    <div>
                        <span class="badge badge-info"><small><?php echo JHtml::date($this->story->item_date); ?></small></span>
                        <div style="margin-top: 5px;"><?php echo $this->story->item_title; ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <div class="btn-group">
                    <?php if ($this->prevStory): ?>
                        <a class="btn btn-secondary" href="<?php echo FactoryRouteRss::view('toolbar&tmpl=component&story_id=' . $this->prevStory); ?>">
                            <?php echo FactoryTextRss::_('toolbar_prev_story_tooltip'); ?>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary disabled">
                            <?php echo FactoryTextRss::_('toolbar_prev_story_tooltip'); ?>
                        </button>
                    <?php endif; ?>

                    <?php if ($this->nextStory): ?>
                        <a class="btn btn-secondary" href="<?php echo FactoryRouteRss::view('toolbar&tmpl=component&story_id=' . $this->nextStory); ?>">
                            <?php echo FactoryTextRss::_('toolbar_next_story_tooltip'); ?>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary disabled">
                            <?php echo FactoryTextRss::_('toolbar_next_story_tooltip'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <a class="btn btn-warning" href="<?php echo FactoryRouteRss::view('toolbar&tmpl=component'); ?>"><?php echo FactoryTextRss::_('toolbar_random_story_tooltip'); ?></a>
                <a class="btn btn-info" href="<?php echo FactoryRouteRss::_(''); ?>">
                    <?php echo FactoryTextRss::_('toolbar_back_to_site'); ?>
                </a>
            </div>
        </div>
    </div>
    <div style="flex: auto;">
        <div style="width: 100%; height: 100%;">
            <?php if ($this->story): ?>
                <iframe src="<?php echo $this->story->item_link; ?>" style="width: 100%; height: 100%; border: 0;" width="100%"></iframe>
            <?php endif; ?>
        </div>
    </div>
</div>
