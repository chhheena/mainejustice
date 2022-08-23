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

jimport('phputf8.utils.validation');

class JRSSFactoryProParser
{
    public $parserName = null;
    public $parser = null;
    public $xmlEncoding = '';
    protected $featuredArticles = false;
    private $error = '';

    public function __construct($parserName)
    {

        $parserName = strtolower($parserName);
        $this->parserName = $parserName;

        $parserDir = RSS_FACTORY_COMPONENT_PATH . DS . 'parsers' . DS . $this->parserName;

        if (!file_exists($parserDir)) {
            throw new Exception('Parser ' . strtoupper($this->parserName) . ' not found! No feeds refreshed!', 500);
            return;
        }

        switch ($parserName) {
            case 'simplepie':
                require_once($parserDir . DS . 'simplepie.inc');
                require_once($parserDir . DS . 'idn' . DS . 'idna_convert.class.php');
                $this->parser = new SimplePie();
                $this->parser->enable_cache(false);
                break;
            case 'magpie':
                require_once($parserDir . DS . 'rss_fetch.inc');
                define('MAGPIE_CACHE_ON', false);
                define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
                $this->parser = new MagpieRSSParser;
                break;
        }

        return $this->parser;
    }

    public static function getInstance($parserName = 'simplepie')
    {
        static $parser;

        if (!is_object($parser) || (is_object($parser) && $parser->parserName != $parserName)) {
            $parser = new JRSSFactoryProParser($parserName);
        }
        return $parser;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setEncoding()
    {
        switch ($this->parserName) {
            case 'simplepie':
                $this->xmlEncoding = $this->parser->get_encoding();
                break;
            case 'domit':
                //domit doesn't seem to have a method to retreive xml's encoding
                $this->xmlEncoding = 'UTF-8';
                break;
            case 'magpie':
                $this->xmlEncoding = 'UTF-8';
                break;
        }
    }

    public function getEncoding()
    {
        return $this->xmlEncoding;
    }

    public function parse2cache($feed)
    {
        if (!$this->parser) {
            return;
        }

        $this->archiveStoriesForFeed($feed->id);
        $this->deleteArchivedStoriesForFeed($feed->id);

        if ('http' == $feed->protocol && !$this->fetchURL($feed->url)) {
            return false;
        }

        if ('ftp' == $feed->protocol && !$this->fetchFTP($feed)) {
            return false;
        }

        $method = $this->parserName . '2cache';

        $results = $this->$method($feed);

        $this->error = '';

        return $results;
    }

    protected function fetchURL($url)
    {
        if (!$this->parser) {
            return;
        }

        switch ($this->parserName) {
            case 'simplepie':
                $this->parser->set_feed_url($url);
                $this->parser->enable_order_by_date(true);

                if (!$this->parser->init()) {
                    $this->error = $this->parser->error;
                    return false;
                }

                break;
            case 'magpie':
                if (!$this->parser->fetch_rss($url)) {
                    return false;
                }

                break;
        }

        $this->setEncoding();

        return true;
    }

    protected function fetchFTP($rssSource)
    {
        if (!$this->parser) {
            return;
        }

        $data = $this->getFtpFeed($rssSource);

        switch ($this->parserName) {
            case 'simplepie':
                $this->parser->set_raw_data($data);
                if (!$this->parser->init()) {
                    return false;
                }
                break;
            default:
                return false;
        }

        $this->setEncoding();

        return true;
    }

    protected function simplepie2cache($feed)
    {
        // Initialise variables.
        $data = array();
        $cachedItems = 0;
        $settings = JComponentHelper::getParams('com_rssfactory');

        // Set title, description and link.
        $data['channel_title'] = $this->convert2utf8($this->parser->get_title());
        $data['channel_description'] = $this->convert2utf8($this->parser->get_description());
        $data['channel_link'] = $this->parser->get_link();

        // Get items.
        $limit = $this->parser->get_item_quantity($feed->nrfeeds);
        $items = $this->parser->get_items(0, $limit);

        JLoader::register('Import2ContentHelper', JPATH_COMPONENT_SITE . '/parsers/import2content.php');

        JPluginHelper::importPlugin('finder');

        // Parse items.
        foreach ($items as $item) {
            $title = $item->get_title();
            $description = $item->get_description();

            if (!$settings->get('i2c_convert_html_chars', 1)) {
                $title = html_entity_decode($title);
                $description = html_entity_decode($description);
            }

            $data['id']               = null;
            $data['item_title']       = $title;
            $data['item_description'] = $description;
            $data['item_date']        = JFactory::getDate($item->get_date('U'))->toSql();
            $data['rssid']            = $feed->id;
            $data['rssurl']           = $feed->url;
            $data['item_link']        = $item->get_link();
            $data['item_source']      = $item->get_feed()->get_title();
            $data['item_enclosure']   = $this->getEnclosures($item);

            /* @var $cache RssFactoryTableCache */
            $cache = JTable::getInstance('Cache', 'RssFactoryTable');
            $cache->setFeed($feed);

            if (!$cache->save($data)) {
                continue;
            }

            \Joomla\CMS\Factory::getApplication()->triggerEvent('onFinderAfterSave', array(
                'com_rssfactory.story',
                $cache,
                true
            ));

            // Import 2 Content.
            if (class_exists('Import2ContentHelper')) {
                $article = Import2ContentHelper::storeArticle($feed, $cache);

                if ($article && $article->featured) {
                    $this->featuredArticles = true;
                }
            }

            $cachedItems++;
        }

        if ($this->featuredArticles && class_exists('Import2ContentHelper')) {
            Import2ContentHelper::reorderFeaturedArticles();
        }

        return $cachedItems;
    }

    protected function magpie2cache(&$rssSource)
    {

        $database = &JFactory::getDBO();
        $config =& RFProHelper::getConfig();

        $resp =& $this->parser->resp;
        $items = $resp->items;

        $nrItems = count($items);
        $limit = $rssSource->nrfeeds;
        if ($limit == 0 || $limit > $nrItems) {
            $limit = $nrItems;
        }

        $row = new JRSSFactoryPRO_Cache($database);
        $now = date("Y-m-d H:i:s", time());

        $row->channel_title = isset($resp->channel['title']) ? ($resp->channel['title']) : '';
        $row->channel_title = $this->convert2utf8($row->channel_title);

        $row->channel_description = isset($resp->channel['description']) ? ($resp->channel['description']) : '';
        $row->channel_description = $this->convert2utf8($row->channel_description);

        $row->channel_link = isset($resp->channel['link']) ? $resp->channel['link'] : '';

        $nrCachedItems = 0;
        for ($i = 0; $i < $limit; $i++) {
            $item = $items[$i];

            $row->id = null;

            //prepare item title
            $row->item_title = isset($item['title']) ? ($item['title']) : '';
            $row->item_title = $this->convert2utf8($row->item_title);
            $row->item_title = htmlentities($row->item_title, ENT_QUOTES, 'UTF-8');

            //prepare item description
            $row->item_description = isset($item['description']) ? ($item['description']) : '';
            $row->item_description = $this->convert2utf8($row->item_description);
            $row->item_description = preg_replace('/<a /', '<a target="_blank" rel="nofollow" ', $row->item_description);
            $row->item_description = preg_replace('/&gt;a /', '&gt;a target="_blank" rel="nofollow" ', $row->item_description);
            //apply word filters
            if (!$row->preStoreFilter($config)) {
                continue;
            }

            /** check if item already exists in cache */
            $itemResume = $row->item_title . $row->item_description;
            $row->item_hash = sha1($this->prepareFeedSHA1($itemResume));

            $query = "SELECT `id` FROM `#__rssfactory_cache` WHERE `rssid`='" . $rssSource->id . "' AND `item_hash`='" . $row->item_hash . "'";
            $database->setQuery($query);
            $found_id = $database->loadResult();
            if ($found_id) {
                $query = "UPDATE `#__rssfactory_cache` SET `archived`='0' WHERE `id`='" . $found_id . "'";
                $database->setQuery($query);
                $database->execute();
                continue;
            }
            /** */

            $row->rssid = $rssSource->id;
            $row->rssurl = $rssSource->url;

            $publishDate = (isset($item['pubDate']) ? $item['pubDate'] :
                (isset($item['dc']['date']) ? $item['dc']['date'] :
                    (isset($item['dc:date']) ? $item['dc:date'] :
                        null))
            );

            $publishDate = strtotime($publishDate);
            if (!$publishDate) {
                $publishDate = time();
            }
            $row->item_date = date('Y-m-d H:i:s', $publishDate);

            $row->item_link = isset($item['link']) ? $item['link'] : '';
            $row->item_source = isset($item['source']) ? $item['source'] : '';

            $enclosure = isset($item['enclosure']) ? $item['enclosure'] : array();
            $row->item_enclosure = base64_encode(serialize($enclosure));

            //VERY important!!!
            $row->date = gmdate('Y-m-d H:i:s');

            $row->store();

            $this->storeArticle($rssSource, $row);

            $nrCachedItems++;
        }

        return $nrCachedItems;
    }

    protected function convert2utf8(&$string)
    {
        if (!utf8_is_valid($string)) {
            $string = utf8_encode($string);
        }
        return $string;
    }

    protected function getEnclosures($item)
    {
        $enclosures = $item->get_enclosures();
        unset($e);
        $e = array();

        if ($enclosures) {
            foreach ($enclosures as $enclosure) {
                $e[] = \Joomla\Utilities\ArrayHelper::fromObject($enclosure);
            }
        }

        return base64_encode(serialize($e));
    }

    protected function getFtpFeed($feed)
    {
        if ('' == $feed->ftp_host || '' == $feed->ftp_username) {
//      echo FactoryText::_('feed_task_testftp_error_invalid_data');
            return false;
        }

        $ftp = JClientFtp::getInstance($feed->ftp_host, 21);
        $contents = false;

        // Check if provided credentials are valid.
        if (!$ftp->login($feed->ftp_username, $feed->ftp_password)) {
//      echo FactoryText::_('feed_task_testftp_error_invalid_credentials');
            return false;
        }

        if (!$ftp->read($feed->ftp_path, $contents)) {
//      echo FactoryText::_('feed_task_testftp_error_invalid_path');
            return false;
        }

        return $contents;
    }

    protected function archiveStoriesForFeed($id)
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true)
            ->update('#__rssfactory_cache')
            ->set('archived = ' . $dbo->quote(1))
            ->where('rssid = ' . $dbo->quote($id));

        $result = $dbo->setQuery($query)
            ->execute();

        return $result;
    }

    protected function deleteArchivedStoriesForFeed($id)
    {
        $dbo = JFactory::getDbo();

        $configuration = JComponentHelper::getParams('com_rssfactory');
        $interval = $configuration->get('archivedeleteinterval', 2);
        $limit = JFactory::getDate('-' . $interval . ' days')->toSql();

        $query = $dbo->getQuery(true)
            ->delete()
            ->from('#__rssfactory_cache')
            ->where('rssid = ' . $dbo->quote($id))
            ->where('archived = ' . $dbo->quote(1))
            ->where('date < ' . $dbo->quote($limit));

        $result = $dbo->setQuery($query)
            ->execute();

        return $result;
    }
}
