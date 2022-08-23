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

class FactoryModelList extends JModelList
{
    protected $filters = array();
    protected $defaultOrdering = 'title';
    protected $defaultDirection = 'asc';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array_keys($this->getSortFields());
        }

        parent::__construct($config);
    }

    public function getListOrder()
    {
        return $this->state->get('list.ordering');
    }

    public function getListDirn()
    {
        return $this->state->get('list.direction');
    }

    public function getSaveOrder()
    {
        $result = $this->getListOrder() == $this->tableAlias . '.ordering';

        return $result;
    }

    public function getFilterPublished()
    {
        return JHtml::_('jgrid.publishedOptions', array(
            'trash'    => false,
            'archived' => false,
            'all'      => false,
        ));
    }

    public function getFilters()
    {
        return $this->filters;
    }

    protected function addFilterPublished(&$query)
    {
        $published = $this->getState('filter.published');

        if ('' != $published) {
            $query->where($this->tableAlias . '.published = ' . $query->quote($published));
        }
    }

    protected function addOrderResults(&$query)
    {
        $orderCol = $this->state->get('list.ordering', $this->tableAlias . '.' . $this->defaultOrdering);
        $orderDirn = $this->state->get('list.direction', $this->defaultDirection);

        $query->order($query->escape($orderCol . ' ' . $orderDirn));
    }

    protected function populateState($ordering = null, $direction = null)
    {
        if (is_null($ordering)) {
            $ordering = $this->tableAlias . '.' . $this->defaultOrdering;
        }

        if (is_null($direction)) {
            $direction = $this->defaultDirection;
        }

        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        foreach ($this->filters as $filter) {
            $value = $this->getUserStateFromRequest($this->context . '.filter.' . $filter, 'filter_' . $filter, '');
            $this->setState('filter.' . $filter, $value);
        }

        // List state information.
        parent::populateState($ordering, $direction);
    }
}
