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

class RssFactoryBackendModelComments extends FactoryModelList
{
    protected $tableAlias = 'c';
    protected $filters = array('published');
    protected $defaultOrdering = 'created_at';
    protected $defaultDirection = 'desc';

    public function getSortFields()
    {
        return array(
            $this->tableAlias . '.text'       => FactoryTextRss::_('comments_list_text'),
            'cache.item_title'                => FactoryTextRss::_('comments_list_story'),
            'u.username'                      => FactoryTextRss::_('comments_list_username'),
            $this->tableAlias . '.created_at' => FactoryTextRss::_('comments_list_created_at'),
            $this->tableAlias . '.published'  => JText::_('JSTATUS'),
            $this->tableAlias . '.id'         => JText::_('JGRID_HEADING_ID'),
        );
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();

        // Select the comments.
        $query->select($this->tableAlias . '.*')
            ->from('#__rssfactory_comments ' . $this->tableAlias);

        // Select the username.
        $query->select('u.username')
            ->leftJoin('#__users u ON u.id = ' . $this->tableAlias . '.user_id');

        // Select the story.
        $query->select('cache.item_title')
            ->leftJoin('#__rssfactory_cache cache ON cache.id = ' . $this->tableAlias . '.item_id');

        $this->addFilterSearch($query);
        $this->addFilterPublished($query);
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
                $query->where($this->tableAlias . '.text LIKE ' . $search);
            }
        }
    }
}
