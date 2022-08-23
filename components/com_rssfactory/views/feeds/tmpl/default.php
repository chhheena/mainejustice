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

if ($this->searchEnabled): ?>
    <div class="rssfactory-search">
        <form action="<?php echo FactoryRouteRss::view('feeds'); ?>" method="GET">
            <input type="text" class="search-query"
                   placeholder="<?php echo FactoryTextRss::_('feeds_search_box_placeholder'); ?>" name="search"
                   value="<?php echo $this->search; ?>"/>

            <input type="hidden" name="option" value="com_rssfactory"/>
            <input type="hidden" name="view" value="feeds"/>
            <input type="hidden" name="category_id" value="<?php echo @$this->category->id; ?>"/>
        </form>
    </div>
<?php endif; ?>

<h1><?php echo $this->pageTitle; ?></h1>

<?php echo JHtmlRssFactoryFeeds::display($this->items, $this->display, $this->ads); ?>
