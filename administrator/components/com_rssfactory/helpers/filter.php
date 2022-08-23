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

class RssFactoryFilterHelper
{
    private $configuration;

    public function __construct(\Joomla\Registry\Registry $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getWordFilter(RssFactoryTableFeed $feed)
    {
        // Initialise variables.
        $feedFilterState = $feed->enablerefreshwordfilter;
        $globalFilterState = $this->configuration->get('enablerefreshwordfilter', 0);

        // Check if feed has specifically disabled the word filter.
        if (!$feedFilterState) {
            return false;
        }

        // Check if feed is using global word filter state.
        if (-1 == $feedFilterState && !$globalFilterState) {
            return false;
        }

        return array(
            'allowed' => $this->getAllowedWordFilter($feed),
            'banned'  => $this->getBannedWordFilter($feed),
            'exact'   => $this->getExactWordFilter($feed),
        );
    }

    public function getI2CWordFilter(RssFactoryTableFeed $feed)
    {
        // Check if filter is enabled from settings.
        if (!$this->configuration->get('enablei2cwordfilter', 0)) {
            return false;
        }

        // Check if filter is enabled for feed.
        if (!$feed->i2c_enable_word_filter) {
            return false;
        }

        $filter = array(
            'allowed'      => array(),
            'banned'       => array(),
            'exact'        => array(),
            'replacements' => array(),
        );

        // Set allowed and banned filters.
        $whiteList = trim($feed->i2c_words_white_list);
        $blackList = trim($feed->i2c_words_black_list);

        // Set allowed filter.
        if ($whiteList) {
            $filter['allowed'] = strlen($whiteList) ? explode(',', $whiteList) : array();
        } elseif ('' != trim($this->configuration->get('i2callowedwords', ''))) {
            $filter['allowed'] = explode(',', trim($this->configuration->get('i2callowedwords', '')));
        }

        // Set banned filter.
        if ($blackList) {
            $filter['banned'] = strlen($blackList) ? explode(',', $blackList) : array();
        } elseif ('' != trim($this->configuration->get('i2cbannedwords', ''))) {
            $filter['banned'] = explode(',', trim($this->configuration->get('i2cbannedwords', '')));
        }

        // Set exact filter.
        if (trim($feed->i2c_words_exact_list)) {
            $filter['exact'] = explode(',', $feed->i2c_words_exact_list);
        }

        // Set replacements filter.
        if (trim($feed->i2c_words_replacements)) {
            $filter['replacements'] = explode(',', $feed->i2c_words_replacements);
        }

        return $filter;
    }

    private function getAllowedWordFilter(RssFactoryTableFeed $feed)
    {
        $words = array();
        $allowed = $feed->refreshallowedwords;

        if ($feed->params->get('merge_refreshallowedwords', 0)) {
            $allowed = $this->configuration->get('refreshallowedwords') . ',' . $allowed;
        }

        $allowed = explode(',', $allowed);

        foreach ($allowed as $word) {
            $word = trim($word);

            if ('' != $word) {
                $words[] = $word;
            }
        }

        return $words;
    }

    private function getBannedWordFilter(RssFactoryTableFeed $feed)
    {
        $words = array();
        $banned = $feed->refreshbannedwords;

        if ($feed->params->get('merge_refreshbannedwords', 0)) {
            $banned = $this->configuration->get('refreshbannedwords') . ',' . $banned;
        }

        $banned = explode(',', $banned);

        foreach ($banned as $word) {
            $word = trim($word);

            if ('' != $word) {
                $words[] = $word;
            }
        }

        return $words;
    }

    private function getExactWordFilter(RssFactoryTableFeed $feed)
    {
        $words = array();
        $exact = $feed->refreshexactmatchwords;

        if ($feed->params->get('merge_refreshexactmatchwords', 0)) {
            $exact = $this->configuration->get('refreshexactmatchwords') . "\n" . $exact;
        }

        $exact = explode("\n", $exact);

        foreach ($exact as $word) {
            $word = trim($word);

            if ('' != $word) {
                $words[] = $word;
            }
        }

        return $words;
    }
}
