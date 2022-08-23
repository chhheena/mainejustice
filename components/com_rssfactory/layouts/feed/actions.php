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

JLoader::register('JHtmlFeeds', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/html/feeds.php');

extract($displayData);

?>

<?php if ($bookmarks): ?>
    <div class="btn-group pull-left">
        <a class="btn btn-mini dropdown-toggle btn-secondary" data-toggle="dropdown" href="#">
            <?php if ($config['use_favicons']): ?>
                <?php echo JHtml::_('feeds.icon', $feed->id, false, ['height' => '16px', 'width' => '16px']); ?>
            <?php else: ?>
                <i class="icon-cog"></i>
            <?php endif; ?>
            <span class="caret"></span>
        </a>

        <ul class="dropdown-menu">
            <?php if ($bookmarks): ?>
                <li class="bookmark-icon">
                    <a class="story-bookmark dropdown-item"
                       href="<?php echo FactoryRouteRss::task('feed.favorite&favorite=' . intval(!$feed->is_favorite) . '&format=raw&feed_id=' . $feed->id, false); ?>"><!--
                        --><i class="icon-ok pull-right story-bookmark-icon" <?php echo !$feed->is_favorite ? 'style="display: none;"' : ''; ?>></i><!--
                        --><i class="icon-bookmark"></i><?php echo FactoryTextRss::_('feed_action_bookmark'); ?><!--
                    --></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
<?php elseif ($config['use_favicons']): ?>
    <?php echo $html[] = JHtml::_('feeds.icon', $feed->id); ?>
<?php endif; ?>
