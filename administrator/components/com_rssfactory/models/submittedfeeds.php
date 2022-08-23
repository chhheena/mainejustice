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

class RssFactoryBackendModelSubmittedFeeds extends FactoryModelList
{
    protected $tableAlias = 'f';
    protected $defaultOrdering = 'date';
    protected $defaultDirection = 'desc';

    public function getSortFields()
    {
        return array(
            $this->tableAlias . '.title' => JText::_('JGLOBAL_TITLE'),
            $this->tableAlias . '.url'   => FactoryTextRss::_('submittedfeeds_list_url'),
            $this->tableAlias . '.date'  => FactoryTextRss::_('submittedfeeds_list_date'),
            $this->tableAlias . '.id'    => JText::_('JGRID_HEADING_ID'),
        );
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();

        // Select the feeds.
        $query->select($this->tableAlias . '.*')
            ->from('#__rssfactory_submitted ' . $this->tableAlias);

        $this->addFilterSearch($query);
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
}
