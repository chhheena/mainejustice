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

class modRssFactoryHelper
{
    public static function getResults(&$params)
    {
        $filters = array(
            'show_empty_feeds'   => false,
            'stories_sort_order' => $params->get('sort_order', 'none'),
            'stories_sort_dir'   => $params->get('sort_dir', 'DESC'),
            'feeds_limit'        => $params->get('feeds_limit', 3),
            'limit'              => $params->get('stories_limit', 3),
            'categories'         => $params->get('category', array()),
            'interval'           => $params->get('filter_interval', ''),
        );

        $relevant = $params->get('show_relevant_stories', 0);
        if ($relevant) {
            $category = self::getCurrentCategory();

            if ($category) {
                $filters['relevant'] = $category;
            }
        }

        if ($params->get('wordfilter.enabled', 0)) {
            $filters['wordfilter'] = (array)$params->get('wordfilter');
        }

        if ('list' == $params->get('display_mode', 'tiled')) {
            $results = RssFactoryFeedsHelper::getItemsForList($filters);
        } else {
            $results = RssFactoryFeedsHelper::getItemsForTiled($filters);
        }

        return $results;
    }

    public static function getConfig(&$params)
    {
        $config = array(
            'pagination'          => false,
            'show_empty_feeds'    => false,
            'columns'             => 1,
            'mode'                => $params->get('display_mode', 'tiled'),
            'story_title_trim'    => $params->get('story_title_trim', 0),
            'story_desc_trim'     => $params->get('story_desc_trim', 0),
            'description_display' => $params->get('description_display', 'tooltip'),
            'use_favicons'        => $params->get('use_favicons', 1),
            'date'                => $params->get('show_date', 1),
            'voting'              => $params->get('show_rating', 1),
            'comments'            => $params->get('show_comments', 1),
            'bookmarks'           => $params->get('show_bookmarks', 1),
        );

        return $config;
    }

    protected static function getCurrentCategory()
    {
        // Initialise variables.
        $input = JFactory::getApplication()->input;
        $option = $input->getString('option', '');
        $view = $input->getString('view', '');

        if ('com_content' != $option) {
            return false;
        }

        if ('article' == $view) {
            $id = $input->getInt('id', 0);
            $table = JTable::getInstance('Content');
            $table->load($id);

            return $table->catid;
        }

        if ('category' == $view) {
            return $input->getInt('id', 0);
        }

        return false;
    }
}
