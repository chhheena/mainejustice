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

if ($this->category): ?>
    <?php if ('root' != $this->category->id): ?>
        <div class="lead"><?php echo $this->category->title; ?></div>

        <?php if ($this->displayConfig->get('category.description_show', 1) && $this->category->description): ?>
            <div class="muted">
                <?php echo $this->category->description; ?>
            </div>
        <?php endif; ?>

        <?php if ($this->displayConfig->get('category.headlines_show', 1)): ?>
            <?php echo JHtml::_('RssFactoryFeeds.displayStories', $this->category->headlines['stories'], null, array(
                'voting'              => false,
                'comments'            => false,
                'date'                => false,
                'description_display' => 'tooltip',
            )); ?>

            <?php if ($this->displayConfig->get('category.headlines_limit', 5) < $this->category->stories && -1 != $this->displayConfig->get('category.headlines_limit', 5)): ?>
                <a href="<?php echo FactoryRouteRss::view('feeds&category_id=' . $this->category->id); ?>"
                   class="btn btn-small"><?php echo FactoryTextRss::_('categories_category_headlines_more_stories'); ?></a>
            <?php endif; ?>
        <?php endif; ?>

        <hr/>
    <?php endif; ?>

    <?php if ($this->categories): ?>
        <div class="row-fluid">
        <?php foreach ($this->categories as $this->i => $this->category): ?>
            <?php if ($this->i && 0 == $this->i % $this->displayConfig->get('subcategory.columns_display', 3)): ?>
                </div>
                <div class="row-fluid">
            <?php endif; ?>

            <div class="span<?php echo 12 / $this->displayConfig->get('subcategory.columns_display', 3); ?>">
                <?php echo $this->loadTemplate('item'); ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p><?php echo FactoryTextRss::_('cateogry_no_subcategories'); ?></p>
    <?php endif; ?>
<?php else: ?>
    <p><?php echo FactoryTextRss::_('cateogry_not_found'); ?></p>
<?php endif; ?>
