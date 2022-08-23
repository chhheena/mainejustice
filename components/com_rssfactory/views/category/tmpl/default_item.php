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

<div class="lead">
    <?php if ($this->category->children): ?>
        <a href="<?php echo FactoryRouteRss::view('category&category_id=' . $this->category->id); ?>"><?php echo $this->category->title; ?></a>
    <?php else: ?>
        <?php echo $this->category->title; ?>
    <?php endif; ?>
</div>

<?php if ($this->displayConfig->get('subcategory.description_show', 1) && $this->category->description): ?>
    <div class="muted">
        <?php echo $this->category->description; ?>
    </div>
<?php endif; ?>

<?php if ($this->displayConfig->get('subcategory.content_show', 1)): ?>
    <div>
        <?php if ($this->category->children): ?>
            <a href="<?php echo FactoryRouteRss::view('category&category_id=' . $this->category->id); ?>"><?php echo FactoryTextRss::plural('categories_category_subcategories', $this->category->children); ?></a>,
        <?php else: ?>
            <?php echo FactoryTextRss::plural('categories_category_subcategories', $this->category->children); ?>,
        <?php endif; ?>

        <?php if ($this->category->stories): ?>
            <a href="<?php echo FactoryRouteRss::view('feeds&category_id=' . $this->category->id); ?>"><?php echo FactoryTextRss::plural('categories_category_stories', $this->category->stories); ?></a>
        <?php else: ?>
            <?php echo FactoryTextRss::plural('categories_category_stories', $this->category->stories); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($this->displayConfig->get('subcategory.headlines_show', 1)): ?>
    <hr/>
    <?php echo JHtml::_('RssFactoryFeeds.displayStories', $this->category->headlines['stories'], null, array(
        'voting'              => false,
        'comments'            => false,
        'date'                => false,
        'description_display' => 'tooltip',
    )); ?>

    <?php if ($this->displayConfig->get('subcategory.headlines_limit', 5) < $this->category->stories && -1 != $this->displayConfig->get('subcategory.headlines_limit', 5)): ?>
        <a href="<?php echo FactoryRouteRss::view('feeds&category_id=' . $this->category->id); ?>"
           class="btn btn-small"><?php echo FactoryTextRss::_('categories_category_headlines_more_stories'); ?></a>
    <?php endif; ?>
<?php endif; ?>
