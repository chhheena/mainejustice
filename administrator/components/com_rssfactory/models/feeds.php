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

class RssFactoryBackendModelFeeds extends FactoryModelList
{
    protected $tableAlias = 'f';
    protected $filters = array('published', 'category');
    protected $defaultOrdering = 'title';
    protected $defaultDirection = 'asc';

    public function getSortFields()
    {
        return array(
            $this->tableAlias . '.ordering'    => JText::_('JGRID_HEADING_ORDERING'),
            $this->tableAlias . '.published'   => JText::_('JSTATUS'),
            $this->tableAlias . '.title'       => JText::_('JGLOBAL_TITLE'),
            'c.title'                          => JText::_('JCATEGORY'),
            $this->tableAlias . '.date'        => FactoryTextRss::_('feeds_list_last_refresh'),
            $this->tableAlias . '.nrfeeds'     => FactoryTextRss::_('feeds_list_title_nr_feeds'),
            $this->tableAlias . '.rsserror'    => FactoryTextRss::_('feeds_list_had_error'),
            $this->tableAlias . '.url'         => FactoryTextRss::_('feeds_list_url'),
            $this->tableAlias . '.i2c_enabled' => FactoryTextRss::_('feeds_list_i2c_enabled'),
            $this->tableAlias . '.id'          => JText::_('JGRID_HEADING_ID'),
        );
    }

    public function getFilterCategory()
    {
        return JHtml::_('category.options', 'com_rssfactory');
    }

    public function getTotal()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('COUNT(1)')
            ->from('#__rssfactory ' . $this->tableAlias);

        $this->addFilterSearch($query);
        $this->addFilterPublished($query);
        $this->addFilterCategory($query);

        $total = $dbo->setQuery($query)
            ->loadResult();

        return $total;
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();

        // Select the feeds.
        $query
            ->select($this->tableAlias . '.id')
            ->select($this->tableAlias . '.ordering')
            ->select($this->tableAlias . '.title')
            ->select($this->tableAlias . '.published')
            ->select($this->tableAlias . '.url')
            ->select($this->tableAlias . '.nrfeeds')
            ->select($this->tableAlias . '.date')
            ->select($this->tableAlias . '.last_refresh_stories')
            ->select($this->tableAlias . '.i2c_enabled')
            ->select($this->tableAlias . '.rsserror')
            ->select($this->tableAlias . '.last_error')
            ->from('#__rssfactory ' . $this->tableAlias);

        // Select the category.
        $query->select('c.title AS category_title')
            ->leftJoin('#__categories c ON c.id = ' . $this->tableAlias . '.cat AND c.extension = ' . $query->quote('com_rssfactory'));

        // Select the number of stories cached.
        $query->select('COUNT(cache.id) AS storiesCached')
            ->leftJoin('#__rssfactory_cache cache ON cache.rssid = ' . $this->tableAlias . '.id')
            ->group($this->tableAlias . '.id');

        $this->addFilterSearch($query);
        $this->addFilterPublished($query);
        $this->addFilterCategory($query);
        $this->addOrderResults($query);

        return $query;
    }

    protected function addFilterSearch(&$query)
    {
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($this->tableAlias . '.id = ' . (int)substr($search, 3));
            } else {
                $search = $query->quote('%' . $query->escape($search, true) . '%');
                $query->where($this->tableAlias . '.title LIKE ' . $search);
            }
        }
    }

    protected function addFilterCategory(&$query)
    {
        $category = $this->getState('filter.category');

        if ('' != $category) {
            $query->where($this->tableAlias . '.cat = ' . $query->quote($category));
        }
    }
}
