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

class plgSearchRssFactory extends JPlugin
{
    public function onContentSearchAreas()
    {
        static $areas = null;

        if (is_null($areas)) {
            $this->loadLanguage('plg_search_rssfactory');
            $areas = array('com_rssfactory' => JText::_('PLG_RSSFACTORY_SEARCH_AREA_TITLE'));
        }

        return $areas;
    }

    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        $dbo = JFactory::getDbo();

        if (is_array($areas) && $areas) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return array();
            }
        }

        $text = trim($text);
        if ($text == '') {
            return array();
        }

        $query = $dbo->getQuery(true)
            ->select('c.item_title AS title, c.item_date AS created, c.item_link AS href, cat.title AS section, c.item_description AS text, "1" AS browsernav')
            ->from('#__rssfactory_cache c')
            ->leftJoin('#__rssfactory f ON f.id = c.rssid')
            ->leftJoin('#__categories cat ON cat.id = f.cat');

        // Filter results by phrase.
        $query = $this->addFilterSearchQuery($query, $text, $phrase);

        // Order results.
        $query = $this->addOrderQuery($query, $ordering);

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onQuerySearchPlugin', array(
            'com_rssfactory',
            $query,
        ));

        $results = $dbo->setQuery($query)
            ->loadObjectList();

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onResultsSearchPlugin', array(
            'com_rssfactory',
            $results,
        ));

        return $results;
    }

    protected function addOrderQuery($query, $ordering)
    {
        switch ($ordering) {
            case 'oldest':
                $query->order('c.item_date ASC');
                break;

            case 'popular':
                $query->order('c.hits DESC');
                break;

            case 'alpha':
                $query->order('c.item_title ASC');
                break;

            case 'category':
                $query->order('cat.title ASC');
                break;

            case 'newest':
            default:
                $query->order('c.item_date DESC');
                break;
        }

        return $query;
    }

    protected function addFilterSearchQuery($query, $text, $phrase)
    {
        $fields = array('c.item_title', 'c.item_description');

        switch ($phrase) {
            case 'exact':
                $temp = array();
                foreach ($fields as &$field) {
                    $temp[] = $field . ' LIKE ' . $query->quote('%' . $text . '%');
                }

                $where = '(' . implode(') OR (', $temp) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words = explode(' ', $text);
                $array = array();

                foreach ($words as $word) {
                    $temp = array();
                    foreach ($fields as $field) {
                        $temp[] = $field . ' LIKE ' . $query->quote('%' . $word . '%');
                    }

                    $array[] = '(' . implode(') OR (', $temp) . ')';
                }

                $operand = 'all' == $phrase ? 'AND' : 'OR';
                $where = '(' . implode(') ' . $operand . ' (', $array) . ')';
                break;
        }

        $query->where('(' . $where . ')');

        return $query;
    }
}
