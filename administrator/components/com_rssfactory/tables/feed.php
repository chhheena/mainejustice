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

class RssFactoryTableFeed extends JTable
{
    public $enablerefreshwordfilter;
    public $refreshallowedwords;
    public $refreshbannedwords;
    public $refreshexactmatchwords;
    public $i2c_enable_word_filter;
    public $i2c_words_white_list;
    public $i2c_words_black_list;
    public $i2c_words_exact_list;
    public $i2c_words_replacements;
    public $params;
    protected $filter = null;
    protected $filterI2C = null;

    public function __construct(&$db = null)
    {
        if (is_null($db)) {
            $db = JFactory::getDbo();
        }

        parent::__construct('#__rssfactory', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    public function check()
    {
        if (!parent::check()) {
            return false;
        }

        if ('' != $this->url) {
            $this->url = trim($this->url);

            if (false === strpos($this->url, 'http://') &&
                false === strpos($this->url, 'https://')
            ) {
                $this->url = 'http://' . $this->url;
            }
        }

        return true;
    }

    public function getI2CRules()
    {
        $params = is_string($this->params) ? new JRegistry($this->params) : $this->params;

        return (array)$params->get('i2c_rules', array());
    }
}
