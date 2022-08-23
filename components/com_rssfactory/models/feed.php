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

class RssFactoryFrontendModelFeed extends JModelLegacy
{
    public function favorite($id, $value = 1)
    {
        if (!RssFactoryHelper::isUserAuthorised('frontend.favorites')) {
            return false;
        }

        $user = JFactory::getUser();
        $table = $this->getTable('Favorite', 'RssFactoryTable');

        if ($value) {
            $feed = $this->getTable('Feed', 'RssFactoryTable');

            // Check if feed exists.
            if (!$id || !$feed->load($id)) {
                $this->setState('error', FactoryTextRss::_('feed_task_favorite_error_feed_not_found'));
                return false;
            }

            // Check if feed it's published.
            if (!$feed->published) {
                $this->setState('error', FactoryTextRss::_('feed_task_favorite_error_feed_not_found'));
                return false;
            }

            // Check if feed is already bookmarked.
            if ($table->load(array('user_id' => $user->id, 'feed_id' => $id))) {
                $this->setState('error', FactoryTextRss::_('feed_task_favorite_error_feed_already_favorited'));
                return false;
            }

            // Bookmark feed.
            $data = array(
                'user_id' => $user->id,
                'feed_id' => $id,
            );

            if (!$table->save($data)) {
                return false;
            }
        } else {
            // Check if bookmark exists.
            if (!$table->load(array('user_id' => $user->id, 'feed_id' => $id))) {
                $this->setState('error', FactoryTextRss::_('feed_task_favorite_error_feed_not_favorited'));
                return false;
            }

            // Remove bookmark.
            if (!$table->delete()) {
                return false;
            }
        }

        return true;
    }
}
