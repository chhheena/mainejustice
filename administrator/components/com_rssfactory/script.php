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

class com_RssFactoryInstallerScript
{
    protected $option = 'com_rssfactory';

    public function install($parent)
    {
        return true;
    }

    public function uninstall($parent)
    {
        return true;
    }

    public function update($parent)
    {
        return true;
    }

    public function preflight($type, $parent)
    {
        if ('update' == $type) {
            $file = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/rssfactory.xml';
            $data = JInstaller::parseXMLInstallFile($file);
            $this->updateSchemasTable($data);
        }

        return true;
    }

    public function postflight($type, $parent)
    {
        if ('install' == $type) {
            $this->insertSampleData();
            $this->createMenu();
            $this->insertCategories();
        }

        return true;
    }

    protected function insertSampleData()
    {
        $data = array();
        $data[] = '{"table":"#__categories","definition":{"id":"8","asset_id":"36","parent_id":"1","lft":"13","rgt":"16","level":"1","path":"news","extension":"com_rssfactory","title":"News","alias":"news","note":"","description":"","published":"1","checked_out":"0","checked_out_time":"0000-00-00 00:00:00","access":"1","params":"{\"category_layout\":\"\",\"image\":\"\"}","metadesc":"","metakey":"","metadata":"{\"author\":\"\",\"robots\":\"\"}","created_user_id":"900","created_time":"2013-02-27 08:04:02","modified_user_id":"0","modified_time":"0000-00-00 00:00:00","hits":"0","language":"*","version":"1"}}';
        $data[] = '{"table":"#__categories","definition":{"id":"9","asset_id":"37","parent_id":"8","lft":"14","rgt":"15","level":"2","path":"news\/tech","extension":"com_rssfactory","title":"Tech","alias":"tech","note":"","description":"","published":"1","checked_out":"0","checked_out_time":"0000-00-00 00:00:00","access":"1","params":"{\"category_layout\":\"\",\"image\":\"\"}","metadesc":"","metakey":"","metadata":"{\"author\":\"\",\"robots\":\"\"}","created_user_id":"900","created_time":"2013-02-27 08:04:10","modified_user_id":"0","modified_time":"0000-00-00 00:00:00","hits":"0","language":"*","version":"1"}}';
        $data[] = '{"table":"#__rssfactory","definition":{"id":"2","userid":"0","protocol":"http","url":"http:\/\/feeds2.feedburner.com\/Techcrunch","title":"TechCrunch","ordering":"0","published":"1","nrfeeds":"15","cat":"9","date":null,"rsserror":"0","encoding":null,"enablerefreshwordfilter":"0","refreshallowedwords":"","refreshbannedwords":"","refreshexactmatchwords":"","i2c_enabled":"0","i2c_author":"0","i2c_publishing_period":"5","i2c_sectionid":"0","i2c_catid":"2","i2c_frontpage":"0","i2c_published":"1","i2c_prepend":"","i2c_append":"","i2c_full_article":"0","i2c_enable_word_filter":"0","i2c_words_white_list":"","i2c_words_black_list":"","i2c_words_exact_list":"","i2c_words_replacements":"","ftp_host":"","ftp_username":"","ftp_password":"","ftp_path":"","params":"{\"enable_relevant_stories\":\"\",\"relevant_stories_position\":\"\",\"relevant_stories_limit\":\"5\",\"i2c_access_level\":\"5\"}"}}';
        $data[] = '{"table":"#__rssfactory","definition":{"id":"3","userid":"0","protocol":"http","url":"http:\/\/feeds.nytimes.com\/nyt\/rss\/US","title":"NY Times US","ordering":"0","published":"1","nrfeeds":"15","cat":"9","date":null,"rsserror":"0","encoding":null,"enablerefreshwordfilter":"0","refreshallowedwords":"","refreshbannedwords":"","refreshexactmatchwords":"","i2c_enabled":"0","i2c_author":"0","i2c_publishing_period":"5","i2c_sectionid":"0","i2c_catid":"2","i2c_frontpage":"0","i2c_published":"1","i2c_prepend":"","i2c_append":"","i2c_full_article":"0","i2c_enable_word_filter":"0","i2c_words_white_list":"","i2c_words_black_list":"","i2c_words_exact_list":"","i2c_words_replacements":"","ftp_host":"","ftp_username":"","ftp_password":"","ftp_path":"","params":"{\"enable_relevant_stories\":\"\",\"relevant_stories_position\":\"\",\"relevant_stories_limit\":\"5\",\"i2c_access_level\":\"5\"}"}}';

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rssfactory/models', 'RssFactoryBackendModel');
        $model = JModelLegacy::getInstance('Backup', 'RssFactoryBackendModel');

        $model->executeSQL($data);
    }

    protected function updateSchemasTable($data)
    {
        $extension = JTable::getInstance('Extension', 'JTable');
        $componentId = $extension->find(array('type' => 'component', 'element' => $this->option));

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true)
            ->select('s.version_id')
            ->from('#__schemas s')
            ->where('s.extension_id = ' . $dbo->quote($componentId));
        $result = $dbo->setQuery($query)
            ->loadResult();

        if (!$result) {
            $query = $dbo->getQuery(true)
                ->insert('#__schemas')
                ->set('extension_id = ' . $dbo->quote($componentId))
                ->set('version_id = ' . $dbo->quote($data['version']));
        } else {
            $query = $dbo->getQuery(true)
                ->update('#__schemas')
                ->set('version_id = ' . $dbo->quote($data['version']))
                ->where('extension_id = ' . $dbo->quote($componentId));
        }

        $dbo->setQuery($query)
            ->execute();
    }

    private function insertCategories()
    {
        $data = array(
            'parent_id' => 1,
            'level'     => 1,
            'path'      => 'uncategorized',
            'extension' => 'com_rssfactory',
            'title'     => 'Uncategorized',
            'alias'     => 'uncategorized',
            'published' => 1,
            'access'    => 1,
            'language'  => '*',
        );

        try {
            $category = JTable::getInstance('Category');
            $category->setLocation(1, 'last-child');
            $category->save($data);
        } catch (Exception $e) {
        }
    }

    private function createMenu()
    {
        JLoader::register(
            'FactoryMenu',
            JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/menu.php');

        $menu = array(
            'menutype'    => 'rss-factory',
            'title'       => 'Rss Factory',
            'description' => 'Rss Factory Menu',
        );

        $items = array(
            array('title' => 'Feeds', 'link' => 'index.php?option=' . $this->option . '&view=feeds'),
            array('title' => 'Categories', 'link' => 'index.php?option=' . $this->option . '&view=category'),
        );

        $module = array(
            'title' => 'Rss Factory Menu',
        );

        return FactoryMenu::createMenu($menu, $items, $this->option, $module);
    }
}
