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

class plgContentRssFactory extends JPlugin
{
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed.
        if ($context == 'com_finder.indexer') {
            return true;
        }

        // Simple performance check to determine whether bot should process further.
        if (false === strpos($row->text, 'com_rssfactory')) {
            return true;
        }

        $regex = '/{com_rssfactory\s?(.*?)}/im';
        preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);

        if (!$matches) {
            return true;
        }

        JLoader::discover('Factory', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/factory');

        $this->loadAssets();

        foreach ($matches as $match) {
            $token = $this->parseToken($match[1]);
            $html = $this->getHtmlForToken($token, $row);

            $row->text = str_replace($match[0], $html, $row->text);
        }

        JHtml::_('bootstrap.loadCss');
        JHtml::_('bootstrap.framework');

        return true;
    }

    protected function parseToken($string)
    {
        $delimiter = ',';
        $array = array();

        preg_match_all('/([a-zA-Z]+)=\[(.+)\]/Ui', $string, $matches);

        if ($matches) {
            if (false !== $key = array_search('delimiter', $matches[1])) {
                $delimiter = $matches[2][$key];
            }

            foreach ($matches[1] as $id => $key) {
                if ('delimiter' === $key) {
                    continue;
                }

                $array[$key] = array_map('trim', explode($delimiter, $matches[2][$id]));
            }
        }

        return new \Joomla\Registry\Registry($array);
    }

    protected function getHtmlForToken($token, $row)
    {
        // Initialise variables.
        $filters = $this->getFiltersForToken($token, $row);
        $config = $this->getConfigForToken($token);

        // Get results.
        $results = $this->getResults($filters, $config['mode']);

        // Check if any results returned.
        if (!$results) {
            return false;
        }

        $html = array();

        $html[] = '<h3>' . FactoryTextRss::_('relevant_stories_title') . '</h3>';
        $html[] = '<hr />';
        $html[] = JHtml::_('RssFactoryFeeds.display', $results, $config);

        return implode("\n", $html);
    }

    protected function loadAssets()
    {
        // Load language file.
        JFactory::getLanguage()->load('com_rssfactory');

        // Register dependencies.
        JLoader::register('JHtmlRssFactoryFeeds', JPATH_SITE . '/components/com_rssfactory/helpers/html/rssfactoryfeeds.php');
        JLoader::register('RssFactoryHelper', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/rssfactory.php');
        JLoader::register('RssFactoryFeedsHelper', JPATH_SITE . '/components/com_rssfactory/helpers/rssfactoryfeeds.php');
        JLoader::register('FactoryHtml', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
        JLoader::register('FactoryRoute', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
        JLoader::register('FactoryText', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
    }

    protected function getFiltersForToken($token, $row)
    {
        $filters = array();

        // Set category filter.
        if ($filter = $token->get('categoryId', array())) {
            $filters['categories'] = $filter;
        }

        // Set feed filter.
        if ($filter = $token->get('sourceId', array())) {
            $filters['feeds'] = $filter;
        }

        // Set the number of feeds option.
        if ($filter = $token->get('nrFeeds', array())) {
            $filters['feeds_limit'] = $filter[0];
        }

        // Set the number of stories option.
        if ($filter = $token->get('nrStories', array())) {
            $filters['limit'] = $filter[0];
        }

        // Set relevant categories filter.
        if ($filter = $token->get('relevantStories', array())) {
            if ($filter[0]) {
                $filters['relevant'] = $row->catid;
            }
        }

        // Set search filter.
        if ($filter = $token->get('searchTerm', array())) {
            $filters['search'] = $filter;
        }

        return $filters;
    }

    protected function getConfigForToken($token)
    {
        $config = array(
            'pagination' => false,
        );

        $array = array(
            'outputStyle'   => 'description_display',
            'displayMethod' => 'mode',
            'columns'       => 'columns',
            'linkBehavior'  => 'story_source_link_behavior',
            'linkTarget'    => 'story_source_link_target',
        );

        foreach ($array as $key => $val) {
            $option = $token->get($key, false);

            if (false !== $option) {
                $config[$val] = $option;
            }
        }

        if (!isset($config['mode'])) {
            $config['mode'] = JComponentHelper::getParams('com_rssfactory')->get('liststyle', 'tiled');
        } else {
            $config['mode'] = reset($config['mode']);
        }

        $config['show_empty_feeds'] = false;

        return $config;
    }

    protected function getResults($filters, $mode)
    {
        if ('list' == $mode) {
            return RssFactoryFeedsHelper::getItemsForList($filters);
        }

        return RssFactoryFeedsHelper::getItemsForTiled($filters);
    }
}
