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

use Joomla\CMS\MVC\Model\AdminModel;

class RssFactoryBackendModelFeed extends AdminModel
{
    protected $event_after_delete = 'onAfterFeedDelete';

    public $option = 'com_rssfactory';

    /** @var Katzgrau\KLogger\Logger */
    private $logger;

    public function __construct(array $config)
    {
        parent::__construct($config);

        \Joomla\CMS\Factory::getApplication()->registerEvent($this->event_after_delete, $this->event_after_delete);

        if (isset($config['logger'])) {
            $this->logger = $config['logger'];
        }
    }

    public function save($data)
    {
        if (!parent::save($data)) {
            return false;
        }

        if ($this->getState($this->getName() . '.new')) {
            $data['id'] = $this->getState($this->getName() . '.id');
            $this->refreshIcon($data);
        }

        return true;
    }

    public function getTable($type = 'Feed', $prefix = 'RssFactoryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        /* @var $form JForm */
        $form = $this->loadForm(
            $this->option . '.' . $this->getName(),
            $this->getName(),
            array(
                'control'   => 'jform',
                'load_data' => $loadData,
            ),
            false,
            '/form');

        if (empty($form)) {
            return false;
        }

        $form->loadFile($this->getName() . '.import2content', true, '/form');

        $data = $loadData ? $this->loadFormData() : array();
        $this->preprocessForm($form, $data);

        if ($loadData) {
            $form->bind($data);
        }

        return $form;
    }

    public function refresh($pks = null)
    {
        if ($this->logger) {
            $this->logger->info('Starting feeds refresh.');
        }

        $pks = (array)$pks;
        $dbo = $this->getDbo();
        $array = array();
        $configuration = JComponentHelper::getParams('com_rssfactory');
        JLoader::register('RssFactoryTableFeed', JPATH_COMPONENT_ADMINISTRATOR . '/tables/feed.php');

        set_time_limit((int)$configuration->get('refreshscripttimelimit', 180));

        $query = $dbo->getQuery(true)
            ->select('f.*')
            ->from('#__rssfactory f')
            ->order('f.date DESC');

        if ($pks) {
            $query->where('f.id IN (' . implode(',', $pks) . ')');
        } else {
            $query->where('f.published = ' . $dbo->quote(1));
        }

        $rows = $dbo->setQuery($query)
            ->loadAssocList();

        $errorReporting = error_reporting();
        error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_STRICT);

        foreach ($rows as $item) {
            if ($this->logger) {
                $this->logger->info(sprintf('Refreshing feed: %s (id: %d).', $item['url'], $item['id']));
            }

            $row = $this->getTable();
            $row->bind($item);

            $error = false;
            $row->params = new JRegistry($row->params);

            $parser = JRSSFactoryProParser::getInstance();
            $results = $parser->parse2cache($row);
            $lastError = $parser->getError();

            if ($results === false) {
                $error = true;

                if ($this->logger) {
                    $this->logger->warning(sprintf('Error occurred: %s.', $lastError));
                }
            }
            else {
                if ($this->logger) {
                    $this->logger->info(sprintf('Stories added to cache: %d.', $results));
                }
            }

            $table = JTable::getInstance('Feed', 'RssFactoryTable');

            $data = array(
                'id'                   => $row->id,
                'date'                 => JFactory::getDate()->toSql(),
                'rsserror'             => $error,
                'last_error'           => $lastError,
                'last_refresh_stories' => $results,
                'encoding'             => $parser->getEncoding(),
            );

            if ($error && $configuration->get('unpublisherr', 0)) {
                $data['published'] = 0;
            }

            if (!$table->save($data)) {
                return false;
            }

            $array[] = array(
                'feed_id'  => $row->id,
                'feed_url' => $row->url,
                'stories'  => $results,
            );

            if ($this->logger) {
                $this->logger->info('Feed refreshed.');
            }
        }

        $this->setState('refreshed', $array);

        $cache = RssFactoryCache::getInstance();
        $cache->clean();

        error_reporting($errorReporting);

        return true;
    }

    public function clearCache($pks = null)
    {
        $pks = (array)$pks;
        $dbo = $this->getDbo();

        // Retrieve ids that are going to be removed.
        $query = $dbo->getQuery(true)
            ->select('c.id')
            ->from($dbo->qn('#__rssfactory_cache', 'c'))
            ->where('rssid IN (' . implode(',', $pks) . ')');
        $results = $dbo->setQuery($query)
            ->loadAssocList('id');
        $results = array_keys($results);

        // Clear cache.
        $query = $dbo->getQuery(true)
            ->delete()
            ->from('#__rssfactory_cache')
            ->where('rssid IN (' . implode(',', $pks) . ')');

        $result = $dbo->setQuery($query)
            ->execute();

        if (!$result) {
            return false;
        }

        // Trigger finder after delete event.
        JPluginHelper::importPlugin('finder');

        foreach ($results as $id) {
            \Joomla\CMS\Factory::getApplication()->triggerEvent('onFinderAfterDelete', array(
                'com_rssfactory.story',
                $id
            ));
        }

        return true;
    }

    public function testFtp($data)
    {
        if ('' == $data['host'] || '' == $data['username']) {
            $this->setState('error', FactoryTextRss::_('feed_task_testftp_error_invalid_data'));
            return false;
        }

        $ftp = JClientFtp::getInstance($data['host'], 21);
        $contents = false;

        // Check if provided credentials are valid.
        if (!$ftp->login($data['username'], $data['password'])) {
            $this->setState('error', FactoryTextRss::_('feed_task_testftp_error_invalid_credentials'));
            return false;
        }

        if (!$ftp->read($data['path'], $contents)) {
            $this->setState('error', FactoryTextRss::_('feed_task_testftp_error_invalid_path'));
            return false;
        }

        return true;
    }

    public function refreshIcon($data)
    {
        if (!isset($data['url']) || '' == $data['url'] || !isset($data['id']) || !$data['id']) {
            $this->setState('error', FactoryTextRss::_('feed_task_refreshicon_error_invalid_data'));
            return false;
        }

        RssFactoryHelper::getSiteIcon($data['id'], $data['url']);

        JLoader::register('JHtmlFeeds', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/html/feeds.php');

        $temp = JHtml::_('feeds.icon', $data['id'], true);
        $this->setState('feed.icon', $temp);

        return true;
    }

    public function preview($data)
    {
        jimport('phputf8.utils.validation');

        $url = urldecode($data['i2c_rules_preview_story']);
        $rules = isset($data['params']['i2c_rules']) ? $data['params']['i2c_rules'] : array();
        $debug = isset($data['preview_debug']) ? $data['preview_debug'] : 0;

        try {
            $data = RssFactoryHelper::parseFullArticle($url, $rules, $debug);
        } catch (Exception $e) {
            return '<div class="alert alert-danger"><h4>Error</h4>' . $e->getMessage() . '<br />Error code: ' . $e->getCode() . '</div>';
        }

        return $data;
    }

    public function move($pks, $batch)
    {
        // Check if at least one feed was selected.
        if (!$pks) {
            $this->setState('error', FactoryTextRss::_('feed_move_error_no_items_selected'));
            return false;
        }

        // Check if category was selected.
        if (!is_array($batch) || !isset($batch['category_id'])) {
            $this->setState('error', FactoryTextRss::_('feed_move_error_no_category_selected'));
            return false;
        }

        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->update('#__rssfactory')
            ->set('cat = ' . $dbo->quote($batch['category_id']))
            ->where('id IN (' . implode(',', $pks) . ')');
        $result = $dbo->setQuery($query)
            ->execute();

        if (!$result) {
            return false;
        }

        return true;
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $context = $this->option . '.edit.' . $this->getName();
        $data = $app->getUserState($context . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);

        $formName = str_replace('.', '_', $form->getName());

        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($form->getFieldset($fieldset->name) as $field) {
                $fieldName = ($field->group ? $field->group . '_' : '') . $field->fieldname;

                $label = $form->getFieldAttribute($field->fieldname, 'label', '', $field->group);

                if ('' == $label) {
                    $label = JText::_(strtoupper($formName . '_form_field_' . $fieldName . '_label'));
                    $form->setFieldAttribute($field->fieldname, 'label', $label, $field->group);
                }

                $desc = $form->getFieldAttribute($field->fieldname, 'description', '', $field->group);

                if ('' == $desc) {
                    $desc = JText::_(strtoupper($formName . '_form_field_' . $fieldName . '_desc'));
                    $form->setFieldAttribute($field->fieldname, 'description', $desc, $field->group);
                }
            }
        }
    }
}

function onAfterFeedDelete($context, $table = null)
{
    if ($context instanceof \Joomla\Event\Event) {
        $arguments = $context->getArguments();
        $context = $arguments[0];
        $table = $arguments[1];
    }

    if ('com_rssfactory.feed' !== $context) {
        return null;
    }

    $cache = JTable::getInstance('Cache', 'RssFactoryTable');
    $dbo = JFactory::getDbo();

    $query = $dbo->getQuery(true)
        ->delete($dbo->qn($cache->getTableName()))
        ->where($dbo->qn('rssid') . ' = ' . $dbo->q($table->id));

    $dbo->setQuery($query)
        ->execute();

    unlink(JPATH_SITE . '/media/com_rssfactory/icos/ico_' . md5($table->id) . '.png');

    return null;
}
