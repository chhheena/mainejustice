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

class RssFactoryFrontendModelFeeds extends JModelLegacy
{
    protected $option = 'com_rssfactory';
    protected $categoryId = null;
    protected $limit = 5;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $input = JFactory::getApplication()->input;

        $this->setState('list.limit', $input->getInt('limitstart', 0));
        $this->setState('list.feed', $input->getInt('feed_id', 0));
        $this->setState('layout', $input->getString('layout', 'default'));

        $configuration = JComponentHelper::getParams('com_rssfactory');
        $this->limit = $configuration->get('feedsperpage', 10);
    }

    public function getCategory($id = null)
    {
        $input = JFactory::getApplication()->input;
        $id = $input->getInt('category_id', 0);

        if (!$id) {
            return null;
        }

        $category = JCategories::getInstance('RssFactory')->get($id);

        return $category;
    }

    public function getAds()
    {
        return RssFactoryFeedsHelper::getAds();
    }

    public function getItems($display)
    {
        $filters = $this->getFilters();

        if ('list' == $display['mode']) {
            $items = RssFactoryFeedsHelper::getItemsForList($filters);
        }
        else {
            $items = RssFactoryFeedsHelper::getItemsForTiled($filters);
        }

        return $items;
    }

    public function getStories($display)
    {
        $input = JFactory::getApplication()->input;
        $filters = $this->getFilters();

        if ('list' == $display['mode']) {
            $stories = RssFactoryFeedsHelper::getStoriesForList($filters);
        }
        else {
            $id = $input->getInt('feed_id', 0);
            $stories = RssFactoryFeedsHelper::getStoriesForFeed($filters, $id, $this->getStoriesPerPage());
        }

        return $stories;
    }

    public function getPagination()
    {
        $input = JFactory::getApplication()->input;
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $filters = $this->getFilters();

        if ('list' == $configuration->get('liststyle', 'tiled')) {
            $id = $input->getInt('category_id', 0);
            $pagination = RssFactoryFeedsHelper::getPaginationForList($filters);
        } else {
            $id = $input->getInt('feed_id', 0);
            $total = RssFactoryFeedsHelper::getTotalStories($filters);
            $pagination = RssFactoryFeedsHelper::getPaginationForFeed($id, $total, $this->getStoriesPerPage());
        }

        return $pagination;
    }

    public function getSearch()
    {
        return htmlentities(JFactory::getApplication()->input->getString('search', ''));
    }

    public function getSearchEnabled()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        return $configuration->get('showSearch', 1);
    }

    public function getDisplay(Joomla\CMS\Menu\SiteMenu $menu = null)
    {
        $settings = \Joomla\CMS\Component\ComponentHelper::getParams('com_rssfactory');

        $defaults = array(
            'mode' => $settings->get('liststyle', 'list'),
            'description_display' => $settings->get('showfeeddescription', 'tooltip'),
        );
        
        if (null === $active = $menu->getActive()) {
            return $defaults;
        }

        $params = $menu->getActive()->params;
        $menuParams = array();

        if ('global' !== $mode = $params->get('display.mode', 'global')) {
            $menuParams['mode'] = $mode;
        }

        if ('global' !== $description = $params->get('display.description', 'global')) {
            $menuParams['description_display'] = $description;
        }

        return array_merge($defaults, $menuParams);
    }

    protected function getStoriesPerPage()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        return $configuration->get('feedsperpage', 7);
    }

    protected function getFilters()
    {
        $input = JFactory::getApplication()->input;

        $categoryId = $input->getInt('category_id', 0);
        $search = $input->getString('search', '');
        $feedId = $input->getInt('feed_id', 0);

        $filters = array();

        if ('default' == $this->getState('layout')) {
            if ($categoryId) {
                $filters['categories'] = $categoryId;
            }

            if ($feedId) {
                $filters['feeds'] = $feedId;
            }
        }
        else {
            $filters['bookmarked'] = true;
        }

        if ('' != $search) {
            $filters['search'] = $search;
        }

        $menu = JFactory::getApplication()->getMenu()->getActive();
        $params = $menu ? $menu->params : new \Joomla\Registry\Registry();

        $filters['feeds_sort_column'] = $params->get('filters.sort_column', 'ordering');
        $filters['feeds_sort_dir'] = $params->get('filters.sort_dir', 'asc');

        $filters['limitstart'] = $input->getInt('limitstart');

        return $filters;
    }
}
