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

class RssFactoryTableContent extends JTableContent
{
    protected $feed;

    public function setFeed($feed)
    {
        $this->feed = $feed;
    }

    /**
     * @return RssFactoryTableFeed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    public function check()
    {
        $this->prepareTable();

        if (!parent::check()) {
            return false;
        }

        // Check if item passes I2C word filters check.
        if (!$this->checkI2CWordFilters()) {
            return false;
        }

        $this->wordReplacements();
        $this->addReadMore();
        $this->addRelevantStories();
        $this->checkOverwriteArticle();

        return true;
    }

    public function store($updateNulls = false)
    {
        if (!parent::store($updateNulls)) {
            return false;
        }

        // Set article as featured.
        $this->setFeatured();

        return true;
    }

    protected function checkOverwriteArticle()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        // Check if overwrite is enabled.
        if ($configuration->get('i2c_overwrite_articles', 0)) {
            $table = JTable::getInstance('Content', 'JTable');

            // If article already exists, delete it.
            if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid))) {
                $table->delete($table->id);

                // Remove featured entry.
                if ($table->featured) {
                    $this->removeFeatured($table->id);
                }
            }
        }

        return true;
    }

    protected function prepareTable()
    {
        // Set the publish date to now
        $db = $this->getDbo();
        if ($this->state == 1 && (int)$this->publish_up == 0) {
            $this->publish_up = JFactory::getDate()->toSql();
        }

        if ($this->state == 1 && intval($this->publish_down) == 0) {
            $this->publish_down = $db->getNullDate();
        }

        // Increment the content version number.
        $this->version++;

        // Reorder the articles within the category so the new article is first
        if (empty($this->id)) {
            $this->reorder('catid = ' . (int)$this->catid . ' AND state >= 0');
        }

        return true;
    }

    protected function setFeatured()
    {
        if (!$this->featured) {
            return true;
        }

        $dbo = $this->getDbo();
        $query = ' INSERT INTO #__content_frontpage (' . $dbo->quoteName('content_id') . ', ' . $dbo->quoteName('ordering') . ')'
            . ' VALUES (' . $dbo->quote($this->id) . ', 0)';

        return $dbo->setQuery($query)
            ->execute();
    }

    protected function removeFeatured($pk)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->delete()
            ->from('#__content_frontpage')
            ->where('content_id = ' . $dbo->quote($pk));

        return $dbo->setQuery($query)
            ->execute();
    }

    protected function checkI2CWordFilters()
    {
        // Initialise variables.
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $helper = new RssFactoryFilterHelper($configuration);
        $filter = $helper->getI2CWordFilter($this->getFeed());

        // Check if filter is valid.
        if (false === $filter) {
            return true;
        }

        // Check allowed words filter.
        if (!$this->passesAllowedWordsFilter($filter['allowed'])) {
            return false;
        }

        // Check banned words filter.
        if (!$this->passesBannedWordsFilter($filter['banned'])) {
            return false;
        }

        // Check exact words filter.
        if (!$this->passesExactWordsFilter($filter['exact'])) {
            return false;
        }

        return true;
    }

    protected function passesAllowedWordsFilter($filter)
    {
        if (!$filter) {
            return true;
        }

        foreach ($filter as $word) {
            if (false !== \Joomla\String\StringHelper::strpos($this->title . $this->introtext . $this->fulltext, $word)) {
                return true;
            }
        }

        return false;
    }

    protected function passesBannedWordsFilter($filter)
    {
        if (!$filter) {
            return true;
        }

        foreach ($filter as $word) {
            if (false !== \Joomla\String\StringHelper::strpos($this->title . $this->introtext . $this->fulltext, $word)) {
                return false;
            }
        }

        return true;
    }

    protected function passesExactWordsFilter($filter)
    {
        if (!$filter) {
            return true;
        }

        foreach ($filter as $word) {
            if (false === \Joomla\String\StringHelper::strpos($this->title . $this->introtext . $this->fulltext, $word)) {
                return false;
            }
        }

        return true;
    }

    protected function wordReplacements()
    {
        // Initialise variables.
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $helper = new RssFactoryFilterHelper($configuration);
        $filter = $helper->getI2CWordFilter($this->getFeed());

        // Check if filter is valid.
        if (false === $filter || !$filter['replacements']) {
            return true;
        }

        $patterns = array();
        $replacements = array();

        foreach ($filter['replacements'] as $expression) {
            // Check if expression is valid.
            if (!\Joomla\String\StringHelper::strpos($expression, '|')) {
                continue;
            }

            list ($search, $replace) = explode('|', $expression);

            $regExSpecialCharacters = array('.', '^', '$', '*', '+', '?', '{', '}', '\\', '[', ']', '|', '(', ')', ' ', '#');
            $replaceRegExSpecialCharacters = array('\.', '\^', '\$', '\*', '\+', '\?', '\{', '\}', '\\\\', '\[', '\]', '\|', '\(', '\)', '\s*', '\#');
            $wordDelimiterRegExpClass = '[\s\.\;\:\-\/]';

            $patterns[] = '#(' . $wordDelimiterRegExpClass . '+)'
                . str_replace($regExSpecialCharacters, $replaceRegExSpecialCharacters, trim($search))
                . '(' . $wordDelimiterRegExpClass . '*?)#is';
            $replacements[] = '\1' . $replace . '\2';
        }

        $this->title = preg_replace($patterns, $replacements, ' ' . $this->title . ' ');
        $this->introtext = preg_replace($patterns, $replacements, ' ' . $this->introtext . ' ');
        $this->fulltext = preg_replace($patterns, $replacements, ' ' . $this->fulltext . ' ');
    }

    protected function addReadMore()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        if ($configuration->get('i2c_add_read_more', 0)) {
            $limit = $configuration->get('i2c_readmore_options', 50);
            $words = explode(' ', $this->introtext);

            if (count($words) > $limit) {
                $this->introtext = implode(' ', array_slice($words, 0, $limit));
                array_splice($words, 0, $limit);
                $this->fulltext = implode(' ', $words);
            }
        }
    }

    protected function addRelevantStories()
    {
        $params = $this->getFeed()->params;
        $configuration = JComponentHelper::getParams('com_rssfactory');

        // Check if relevant stories are enabled for this article.
        if (0 == $params->get('enable_relevant_stories', -1) ||
            (-1 == $params->get('enable_relevant_stories', -1) && 0 == $configuration->get('enable_relevant_stories'))
        ) {
            return false;
        }

        $limit = '' != $params->get('relevant_stories_limit', '') ? $params->get('relevant_stories_limit', '') : $configuration->get('relevant_stories_limit', 10);
        $position = -1 != $params->get('relevant_stories_position', -1) ? $params->get('relevant_stories_position', -1) : $configuration->get('relevant_stories_position');

        $html = ' <p>{com_rssfactory relevantStories nrStories=[' . $limit . ']}</p> ';

        if (1 == $position) {
            $this->introtext = $html . $this->introtext;
        } else {
            $this->fulltext .= $html;
        }

        return true;
    }
}
