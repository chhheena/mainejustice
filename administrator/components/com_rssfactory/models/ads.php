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

class RssFactoryBackendModelAds extends FactoryModelList
{
    protected $tableAlias = 'a';
    protected $filters = array('published', 'category');
    protected $defaultOrdering = 'title';
    protected $defaultDirection = 'asc';

    public function getSortFields()
    {
        return array(
            $this->tableAlias . '.title'     => JText::_('JGLOBAL_TITLE'),
            $this->tableAlias . '.published' => JText::_('JSTATUS'),
            $this->tableAlias . '.id'        => JText::_('JGRID_HEADING_ID'),
        );
    }

    public function getFilterCategory()
    {
        return JHtml::_('category.options', 'com_rssfactory');
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();

        // Select the ads.
        $query->select($this->tableAlias . '.*')
            ->from('#__rssfactory_ads ' . $this->tableAlias);

        // Select the assigned categories.
        $query->select('GROUP_CONCAT(CAST(c.title AS CHAR) SEPARATOR ", ") AS categories')
            ->leftJoin('#__rssfactory_ad_category_map map ON map.adId = ' . $this->tableAlias . '.id')
            ->leftJoin('#__categories c ON c.id = map.categoryId AND c.extension = ' . $query->quote('com_rssfactory'))
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
            $query->leftJoin('#__rssfactory_ad_category_map map_filter ON map_filter.adId = ' . $this->tableAlias . '.id')
                ->where('map_filter.categoryId = ' . $query->quote($category));
        }
    }
}
