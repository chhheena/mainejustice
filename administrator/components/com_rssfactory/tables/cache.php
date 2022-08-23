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

class RssFactoryTableCache extends JTable
{
    public $item_description;
    protected $feed;
    protected $debug = false;

    public function __construct(&$db)
    {
        parent::__construct('#__rssfactory_cache', 'id', $db);
    }

    public function setFeed($feed)
    {
        $this->feed = $feed;
    }

    public function getFeed()
    {
        return $this->feed;
    }

    public function getItemDescription()
    {
        $description = $this->item_description;

        // Strip tags.
        $description = $this->stripTags($description);

        // Add source link.
        $description = $this->addSourceLink($description);

        return $description;
    }

    public function check()
    {
        if (!parent::check()) {
            return false;
        }

        // Set item hash.
        if (is_null($this->item_hash)) {
            $this->item_hash = $this->getItemHash();
        }

        // Check if story already exists.
        if (!$this->debug && $this->storyExists()) {
            return false;
        }

        // Set item date.
        if (is_null($this->item_date)) {
            $this->item_date = JFactory::getDate()->toSql();
        }

        // Set date.
        if (is_null($this->date)) {
            $this->date = JFactory::getDate()->toSql();
        }

        $this->encodeUrl();
        $this->parseLinksInDescription();

        // Check if item passes word filters check.
        if (!$this->checkWordFilters()) {
            return false;
        }

        return true;
    }

    public function getItemHash()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        switch ($configuration->get('detectduplicates', 'title_description')) {
            case 'title':
                $hash = $this->item_title;
                break;

            case 'description':
                $hash = $this->item_description;
                break;

            case 'pubdate':
                $hash = $this->item_date;
                break;

            default:
            case 'title_description':
                $hash = $this->item_title . $this->item_description;
                break;
        }

        return sha1(preg_replace('#\s+#', '', $hash));
    }

    protected function storyExists()
    {
        $table = JTable::getInstance('Cache', 'RssFactoryTable');

        $result = $table->load(array(
            'rssid'     => $this->rssid,
            'item_hash' => $this->getItemHash(),
        ));

        if ($result) {
            $table->archived = 0;
            $table->item_date = $this->item_date;

            $table->store();

            return true;
        }

        return false;
    }

    protected function encodeUrl()
    {
        $uri = JURI::getInstance($this->item_link);
        $query = $uri->getQuery(true);

        $uri->setQuery(null);
        $uri->setQuery($query);

        $this->item_link = $uri->toString();

        return true;
    }

    protected function parseLinksInDescription()
    {
        $this->item_description = preg_replace('/<a /', '<a target="_blank" rel="nofollow" ', $this->item_description);
        $this->item_description = preg_replace('/&gt;a /', '&gt;a target="_blank" rel="nofollow" ', $this->item_description);

        return true;
    }

    protected function checkWordFilters()
    {
        // Initialise variables.
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $helper = new RssFactoryFilterHelper($configuration);
        $filter = $helper->getWordFilter($this->getFeed());

        $text = $this->item_title . ' ' . $this->item_description;

        // Check if filter is valid.
        if (false === $filter) {
            return true;
        }

        // Check allowed words filter.
        if (!$this->passesAllowedWordsFilter($text, $filter['allowed'])) {
            return false;
        }

        // Check banned words filter.
        if (!$this->passesBannedWordsFilter($text, $filter['banned'])) {
            return false;
        }

        // Check exact words filter.
        if (!$this->passesExactWordsFilter($text, $filter['exact'])) {
            return false;
        }

        return true;
    }

    protected function passesAllowedWordsFilter($text, $filter)
    {
        if (!$filter) {
            return true;
        }

        foreach ($filter as $word) {
            if (preg_match('/\b' . $word . '\b/iu', $text)) {
                return true;
            }
        }

        return false;
    }

    protected function passesBannedWordsFilter($text, $filter)
    {
        foreach ($filter as $word) {
            if (preg_match('/\b' . $word . '\b/iu', $text)) {
                return false;
            }
        }

        return true;
    }

    protected function passesExactWordsFilter($text, $filter)
    {
        foreach ($filter as $word) {
            if (!preg_match('/\b' . $word . '\b/iu', $text)) {
                return false;
            }
        }

        return true;
    }

    protected function stripTags($text)
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        if ($configuration->get('i2c_strip_html_tags', 0)) {
            $allowed = trim($configuration->get('i2c_allowed_html_tags', ''));

            if ('' != $allowed) {
                $text = strip_tags($text, '<' . implode('>,<', explode(',', $allowed)) . '>');
            }
        }

        return $text;
    }

    protected function addSourceLink($text)
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        if ($configuration->get('i2c_add_source_link', 0)) {
            $attribs = $configuration->get('open_article_source_new_window', 1) ? 'target="_blank"' : '';

            $text .= '<br />' . JHTML::link($this->item_link, FactoryTextRss::_('import_article_source_link_text'), $attribs);
        }

        return $text;
    }
}
