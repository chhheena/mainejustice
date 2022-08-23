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

<h1><?php echo FactoryTextRss::_('feeds_bookmarked_title'); ?></h1>

<hr/>

<?php if ($this->items): ?>
    <?php echo JHtml::_('RssFactoryFeeds.display', $this->items); ?>
<?php else: ?>
    <p><?php echo FactoryTextRss::_('feeds_bookmarks_no_bookmarks_found'); ?></p>
<?php endif; ?>
