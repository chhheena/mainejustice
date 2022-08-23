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

class RssFactoryFeedsHelper
{
    /**
     * Returns the feeds (with stories and pagination for each feed) for the
     * tiled output.
     *
     * @param array $filters
     * @return mixed
     */
    public static function getItemsForTiled($filters = array())
    {
        static $items = array();

        // Initialise variables.
        $filters = self::prepareFilters($filters);

        $hash = md5(serialize($filters));

        if (!isset($items[$hash])) {
            // Get feeds.
            $results = self::getFeeds($filters);

            // Parse results and get stories for each feed.
            foreach ($results as &$result) {
                $result->stories = self::getStoriesForFeed($filters, $result->id, $filters['limit']);
                $result->pagination = self::getPaginationForFeed($result->id, $result->stories_total, $filters['limit']);
            }

            $items[$hash] = $results;
        }

        return $items[$hash];
    }

    /**
     * Returns the stories and pagination for the list output.
     *
     * @param array $filters
     * @return array
     */
    public static function getItemsForList($filters = array())
    {
        static $items = array();

        $filters = self::prepareFilters($filters);

        $hash = md5(serialize($filters));

        if (!isset($items[$hash])) {
            $items[$hash] = array(
                'stories'    => self::getStoriesForList($filters),
                'pagination' => self::getPaginationForList($filters),
            );
        }

        return $items[$hash];
    }

    public static function getStoriesForFeed($filters, $feedId, $limit)
    {
        $filters = self::prepareFilters($filters);

        // Initialise variables.
        $dbo = JFactory::getDbo();

        // Get base query.
        $query = $dbo->getQuery(true)
            ->select('c.*')
            ->from('#__rssfactory_cache c')
            ->where('c.rssid = ' . $dbo->quote($feedId))
            ->group('c.id');

        // Order the stories.
        $query = self::addQueryOrderCondition($query, $filters);

        // Select the vote value.
        $query = self::addQuerySelectVoteValue($query);

        // Filter by search.
        $query = self::addQuerySearchCondition($query, $filters);

        // Filter by interval.
        $query = self::addQueryIntervalCondition($query, $filters['interval']);

        // Filter by word filter.
        $query = self::addQueryWordFilter($query, $filters['wordfilter']);

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onQueryStoriesForFeed', array(
            'com_rssfactory',
            $query,
        ));

        // Get results.
        $results = $dbo->setQuery($query, self::getLimitstartForFeed($feedId), $limit)
            ->loadObjectList('id');

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onResultsStoriesForFeed', array(
            'com_rssfactory',
            $results,
        ));

        $results = self::getCommentsAndVotesForStories($results);

        return $results;
    }

    public static function getPaginationForFeed($feedId, $totalStories, $limit)
    {
        $pagination = new JPagination($totalStories, self::getLimitstartForFeed($feedId), $limit);

        $pagination->setAdditionalUrlParam('feed_id', $feedId);

        return $pagination;
    }

    public static function getTotalStories($filters)
    {
        $dbo = JFactory::getDbo();
        $filters = self::prepareFilters($filters);

        $query = $dbo->getQuery(true)
            ->select('COUNT(c.id)')
            ->from('#__rssfactory_cache c');

        // Filter by feeds.
        if (isset($filters['feeds']) && $filters['feeds']) {
            $query->where('c.rssid IN (' . implode(',', $filters['feeds']) . ')');
        }

        // Filter by search.
        $query = self::addQuerySearchCondition($query, $filters);

        $result = $dbo->setQuery($query)
            ->loadResult();

        return $result;
    }

    public static function getStoriesForList($filters)
    {
        $cache = RssFactoryCache::getInstance();
        $hash = md5('stories_for_list_' . serialize($filters));
        $results = $cache->get($hash);

        if (false === $results) {
            $dbo = JFactory::getDbo();
            $app = JFactory::getApplication();
            $filters = self::prepareFilters($filters);
            $limitstart = $app->input->getInt('limitstart');

            // Get base query.
            $query = self::getBaseQueryForList($filters)
                ->select('c.*');

            // Order the stories.
            $query = self::addQueryOrderCondition($query, $filters);

            // Filter by word filter.
            $query = self::addQueryWordFilter($query, $filters['wordfilter']);

            \Joomla\CMS\Factory::getApplication()->triggerEvent('onQueryStoriesForFeed', array(
                'com_rssfactory',
                $query,
            ));

            // Get results.
            $results = $dbo->setQuery($query, $limitstart, $filters['limit'])
                ->loadObjectList('id');

            \Joomla\CMS\Factory::getApplication()->triggerEvent('onResultsStoriesForFeed', array(
                'com_rssfactory',
                $results,
            ));
        } else {
            $results = unserialize($results);
        }

        $results = self::getCommentsAndVotesForStories($results);
        $results = self::getVotesForStories($results);

        $cache->store(serialize($results), $hash);

        return $results;
    }

    public static function getPaginationForList($filters)
    {
        static $totals = array();

        $dbo = JFactory::getDbo();
        $app = JFactory::getApplication();
        $filters = self::prepareFilters($filters);
        $limitstart = $app->input->getInt('limitstart');

        // Get total.
        $query = self::getBaseQueryForList($filters)
            ->select('COUNT(c.id)');

        $hash = md5($query->dump());

        if (!isset($totals[$hash])) {
            $totals[$hash] = $dbo->setQuery($query)
                ->loadResult();
        }

        return new JPagination($totals[$hash], $limitstart, $filters['limit']);
    }

    public static function getAds()
    {
        $dbo = JFactory::getDbo();
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $input = JFactory::getApplication()->input;
        $categoryId = $input->get('category_id', 0);

        if (!$configuration->get('enable_ads', 1) || !$categoryId) {
            return array();
        }

        $query = $dbo->getQuery(true)
            ->select('a.*')
            ->from('#__rssfactory_ads a')
            ->leftJoin('#__rssfactory_ad_category_map m ON m.adId = a.id')
            ->where('a.categories_assigned = ' . $dbo->quote(''), 'OR')
            ->where('m.categoryId = ' . $dbo->quote($categoryId));
        $results = $dbo->setQuery($query)
            ->loadObjectList();

        return $results;
    }

    public static function getRelevantCategories($joomlaCategories)
    {
        $dbo = JFactory::getDbo();
        $array = array();

        $query = $dbo->getQuery(true)
            ->select('c.id, c.params')
            ->from('#__categories c')
            ->where('c.extension = ' . $dbo->quote('com_rssfactory'));
        $results = $dbo->setQuery($query)
            ->loadObjectList();

        foreach ($results as $result) {
            $params = new JRegistry($result->params);
            $categories = $params->get('relevant_categories', array());

            if (array_intersect($joomlaCategories, $categories)) {
                $array[] = $result->id;
            }
        }

        return $array;
    }

    protected static function prepareFilters($filters)
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $array = array(
            'categories' => 'array',
            'feeds'      => 'array',
            'relevant'   => 'array',
            'search'     => 'array',
        );

        foreach ($array as $item => $type) {
            if (!isset($filters[$item])) {
                $filters[$item] = array();
            }

            if ('array' == $type && !is_array($filters[$item])) {
                $filters[$item] = array($filters[$item]);
            }
        }

        // Set the limit filter.
        if (!isset($filters['limit'])) {
            $filters['limit'] = $configuration->get('feedsperpage', 7);
        }

        // Set the limit filter.
        if (!isset($filters['show_empty_feeds'])) {
            $filters['show_empty_feeds'] = $configuration->get('showemptysources', 0);
        }

        // Set the stories sort order.
        if (!isset($filters['stories_sort_order'])) {
            $filters['stories_sort_order'] = 'item_date';
        }

        // Set the stories sort direction.
        if (!isset($filters['stories_sort_dir'])) {
            $filters['stories_sort_dir'] = 'DESC';
        }

        // Set the feeds limit.
        if (!isset($filters['feeds_limit'])) {
            $filters['feeds_limit'] = 0;
        }

        // Set the date interval.
        if (!isset($filters['interval'])) {
            $filters['interval'] = false;
        }

        // Set the bookmarked filter.
        if (!isset($filters['bookmarked'])) {
            $filters['bookmarked'] = false;
        }

        // Set the feeds sort column.
        if (!isset($filters['feeds_sort_column'])) {
            $filters['feeds_sort_column'] = 'ordering';
        }

        // Set the feeds sort direction.
        if (!isset($filters['feeds_sort_dir'])) {
            $filters['feeds_sort_dir'] = 'asc';
        }

        if (!isset($filters['wordfilter']['any'])) {
            $filters['wordfilter']['any'] = '';
        }

        if (!isset($filters['wordfilter']['exact'])) {
            $filters['wordfilter']['exact'] = '';
        }

        if (!isset($filters['wordfilter']['none'])) {
            $filters['wordfilter']['none'] = '';
        }

        if (!isset($filters['limitstart'])) {
            $filters['limitstart'] = 0;
        }

        return $filters;
    }

    protected static function getTotalCommentsForStories($stories)
    {
        if (!$stories) {
            return array();
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true)
            ->select('COUNT(c.id) AS comments_total, c.item_id')
            ->from('#__rssfactory_comments c')
            ->where('c.type_id = ' . $dbo->quote(1))
            ->where('c.item_id IN (' . implode(',', $stories) . ')')
            ->group('c.item_id');

        // Filter published comments.
        $configuration = JComponentHelper::getParams('com_rssfactory');
        if ($configuration->get('approveComments', 0)) {
            $query->where('c.published = ' . $dbo->quote(1));
        }

        $results = $dbo->setQuery($query)
            ->loadObjectList('item_id');

        return $results;
    }

    protected static function getTotalVotesForStories($stories)
    {
        if (!$stories) {
            return array();
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true)
            ->select('SUM(v.voteValue) AS votes_total, v.cacheId')
            ->from('#__rssfactory_voting v')
            ->where('v.cacheId IN (' . implode(',', $stories) . ')')
            ->group('v.cacheId');

        $results = $dbo->setQuery($query)
            ->loadObjectList('cacheId');

        return $results;
    }

    protected static function getLimitstartForFeed($feedId)
    {
        $input = JFactory::getApplication()->input;
        $limitstart = $input->getInt('limitstart', 0);
        $feed_id = $input->getInt('feed_id', 0);

        if ($feed_id != $feedId) {
            return 0;
        }

        return $limitstart;
    }

    protected static function getCommentsAndVotesForStories($stories)
    {
        // Initialise variables.
        $votes = array();
        $comments = array();

        // Get total number of votes for stories.
        if ($stories && RssFactoryHelper::isUserAuthorised('frontend.voting')) {
            $votes = self::getTotalVotesForStories(array_keys($stories));
        }

        // Get total number of comments for stories.
        if ($stories && RssFactoryHelper::isUserAuthorised('frontend.comment.view')) {
            $comments = self::getTotalCommentsForStories(array_keys($stories));
        }

        // Parse results.
        foreach ($stories as $id => &$story) {
            $story->votes_total = isset($votes[$id]) ? $votes[$id]->votes_total : 0;
            $story->comments_total = isset($comments[$id]) ? $comments[$id]->comments_total : 0;
        }

        return $stories;
    }

    protected static function getVotesForStories($stories)
    {
        if ($stories && RssFactoryHelper::isUserAuthorised('frontend.voting')) {
            $dbo = JFactory::getDbo();
            $user = JFactory::getUser();
            $app = JFactory::getApplication();
            $hash = sha1($user->id . $app->input->server->getString('REMOTE_ADDR', ''));

            $query = $dbo->getQuery(true)
                ->select('v.voteValue AS vote_value, v.cacheId')
                ->from('#__rssfactory_voting v')
                ->where('v.cacheId IN (' . implode(',', array_keys($stories)) . ')')
                ->where('v.voteHash = ' . $dbo->q($hash));
            $results = $dbo->setQuery($query)
                ->loadAssocList('cacheId');

            foreach ($stories as $id => $story) {
                $story->vote_value = isset($results[$id]) ? $results[$id]['vote_value'] : null;
            }
        }

        return $stories;
    }

    protected static function getBaseQueryForList($filters)
    {
        // Initialise variables.
        $dbo = JFactory::getDbo();

        // Get base query.
        $query = $dbo->getQuery(true)
            ->from('#__rssfactory_cache c');

        // Filter by categories.
        if ($filters['categories']) {
            $query->leftJoin('#__rssfactory f ON f.id = c.rssid')
                ->where('f.cat IN (' . implode(',', $filters['categories']) . ')');
        }

        // Filter by feeds.
        if ($filters['feeds']) {
            $query->where('c.rssid IN (' . implode(',', $filters['feeds']) . ')');
        }

        // Filter by relevant Joomla categories.
        if ($filters['relevant'] && $categories = self::getRelevantCategories($filters['relevant'])) {
            $query->leftJoin('#__rssfactory f ON f.id = c.rssid')
                ->where('f.cat IN (' . implode(',', $categories) . ')');
        }

        // Filter by search.
        $query = self::addQuerySearchCondition($query, $filters);

        return $query;
    }

    protected static function getFeeds($filters)
    {
        // Initialise variables.
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $filters = self::prepareFilters($filters);

        // Get base query.
        $query = $dbo->getQuery(true)
            ->select('f.*')
            ->from('#__rssfactory f');

        // Order feeds.
        $query->order('f.' . $filters['feeds_sort_column'] . ' ' . $filters['feeds_sort_dir']);

        // Get total stories for feed.
        $query->select('COUNT(c.id) AS stories_total')
            ->leftJoin('#__rssfactory_cache c ON c.rssid = f.id')
            ->group('f.id');

        // Filter by published feed.
        $query->where('f.published = ' . $dbo->quote(1));

        // Filter by published category.
        $query->leftJoin('#__categories cat ON cat.id = f.cat')
            ->where('cat.published = ' . $dbo->quote(1));

        // Filter by categories.
        if ($filters['categories']) {
            $query->where('f.cat IN (' . implode(',', $filters['categories']) . ')');
        }

        // Filter by feeds.
        if ($filters['feeds']) {
            $query->where('f.id IN (' . implode(',', $filters['feeds']) . ')');
        }

        // Filter by relevant Joomla categories.
        if ($filters['relevant'] && $categories = self::getRelevantCategories($filters['relevant'])) {
            $query->where('f.cat IN (' . implode(',', $categories) . ')');
        }

        // Filter by only feeds with stories.
        if (!$filters['show_empty_feeds']) {
            $query->having('stories_total > 0');
        }

        // Get favorite status.
        if (RssFactoryHelper::isUserAuthorised('frontend.favorites')) {
            $query->select('fav.id AS is_favorite')
                ->leftJoin('#__rssfactory_favorites fav ON fav.feed_id = f.id AND fav.user_id = ' . $dbo->quote($user->id));

            // Filter by bookmarked feeds.
            if ($filters['bookmarked']) {
                $query->where('fav.id IS NOT NULL');
            }
        }

        // Filter by search.
        $query = self::addQuerySearchCondition($query, $filters);

        // Get results.
        $results = $dbo->setQuery($query, 0, $filters['feeds_limit'])
            ->loadObjectList();

        return $results;
    }

    protected static function addQuerySearchCondition($query, $filters)
    {
        if ($search = $filters['search']) {
            $array = array();
            $dbo = JFactory::getDbo();

            foreach ($search as $item) {
                $array[] = '((c.item_title LIKE ' . $dbo->quote('%' . $item . '%') . ') OR (c.item_description LIKE ' . $dbo->quote('%' . $item . '%') . '))';
            }

            $query->where('(' . implode(' OR ', $array) . ')');
        }

        return $query;
    }

    protected static function addQuerySelectVoteValue($query)
    {
        // Initialise variables.
        $user = JFactory::getUser();
        $app = JFactory::getApplication();

        // Select the vote value.
        if (RssFactoryHelper::isUserAuthorised('frontend.voting')) {
            $hash = sha1($user->id . $app->input->server->getString('REMOTE_ADDR', ''));

            $query->select('v.voteValue AS vote_value')
                ->leftJoin('#__rssfactory_voting v ON v.cacheId = c.id AND v.voteHash = ' . $query->quote($hash));
        }

        return $query;
    }

    protected static function addQueryOrderCondition($query, $filters)
    {
        $direction = $filters['stories_sort_dir'];

        switch ($filters['stories_sort_order']) {
            case '':
            case 'none':
            default:
                $query->order('c.item_date ' . $direction);
                break;

            case 'random':
                $query->order('RAND()');
                break;

            case 'votes':
                $query->leftJoin('#__rssfactory_voting votes ON votes.cacheId = c.id')
                    ->order('SUM(votes.voteValue) ' . $direction);
                break;

            case 'comments':
                $approval = JComponentHelper::getParams('com_rssfactory')->get('approveComments', 0);
                $approval = $approval ? ' AND comments.published = ' . $query->quote(1) : '';

                $query->leftJoin('#__rssfactory_comments comments ON comments.type_id = ' . $query->quote(1) . ' AND comments.item_id = c.id ' . $approval)
                    ->order('COUNT(comments.id) ' . $direction);
                break;

            case 'hits';
                $query->order('c.hits ' . $direction);
                break;
        }

        return $query;
    }

    protected static function addQueryIntervalCondition($query, $interval)
    {
        if (!$interval) {
            return $query;
        }

        switch ($interval) {
            case 'today':
                $date = JFactory::getDate('today')->toSql();
                $query->where('c.item_date >= ' . $query->quote($date));
                break;

            case 'week':
                $date = JFactory::getDate('last Monday')->toSql();
                $query->where('c.item_date >= ' . $query->quote($date));
                break;

            case 'last7days':
                $date = JFactory::getDate('-7 days')->toSql();
                $query->where('c.item_date >= ' . $query->quote($date));
                break;

            case 'month':
                $date = JFactory::getDate(gmmktime(null, null, null, date('m'), 1, date('Y')))->toSql();
                $query->where('c.item_date >= ' . $query->quote($date));
                break;

            case 'year':
                $date = JFactory::getDate(gmmktime(null, null, null, 1, 1, date('Y')))->toSql();
                $query->where('c.item_date >= ' . $query->quote($date));
                break;
        }

        return $query;
    }

    protected static function addQueryWordFilter(JDatabaseQuery $query, $filters)
    {
        // Any word filter.
        $filter = trim($filters['any']);

        if ('' !== $filter) {
            $conditions = array();

            foreach (explode(',', $filter) as $word) {
                $word = trim($word);

                if ('' === $word) {
                    continue;
                }

                $conditions[] = $query->qn('c.item_description') . ' LIKE ' . $query->q('%' . $word . '%');
            }

            if ($conditions) {
                $query->where('(' . implode(' OR ', $conditions) . ')');
            }
        }

        // Exact word filter.
        $filter = trim($filters['exact']);

        if ('' !== $filter) {
            $conditions = array();

            foreach (explode("\n", $filter) as $word) {
                $word = trim($word);

                if ('' === $word) {
                    continue;
                }

                $conditions[] = $query->qn('c.item_description') . ' LIKE ' . $query->q('%' . $word . '%');
            }

            if ($conditions) {
                $query->where('(' . implode(' AND ', $conditions) . ')');
            }
        }

        // None word filter.
        $filter = trim($filters['none']);

        if ('' !== $filter) {
            $conditions = array();

            foreach (explode(',', $filter) as $word) {
                $word = trim($word);

                if ('' === $word) {
                    continue;
                }

                $conditions[] = $query->qn('c.item_description') . ' NOT LIKE ' . $query->q('%' . $word . '%');
            }

            if ($conditions) {
                $query->where('(' . implode(' AND ', $conditions) . ')');
            }
        }

        return $query;
    }
}
