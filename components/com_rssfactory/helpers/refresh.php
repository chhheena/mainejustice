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

// Load Joomla framework.
define('_JEXEC', 1);
defined('_JEXEC') or die;

//ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    if ('172.28.0.1' === $_SERVER['REMOTE_ADDR']) {
        define('JPATH_BASE', realpath('/www/joomla/extensions/rss'));
    }
    else {
        define('JPATH_BASE', dirname(__DIR__) . '/../..');
    }

    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

require_once __DIR__ . '/../vendor/autoload.php';

// Load extension.
$extension = JTable::getInstance('Extension');
$result = $extension->find(array('type' => 'component', 'element' => 'com_rssfactory'));

// Check if extension is installed.
if (!$result) {
    return false;
}

// Define some constants.
define('JDEBUG', 0);
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_rssfactory');
define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/com_rssfactory');

// Initialise application.
$app = JFactory::getApplication('site');

$language = JFactory::getLanguage();
$language->load('com_rssfactory', JPATH_SITE);
$language->load('com_rssfactory', JPATH_ADMINISTRATOR);

$html = array();

// Check for valid credentials.
$type = $app->input->getCmd('type', 'cronjob');
$password = $app->input->getString('password');
$valid = false;

$configuration = JComponentHelper::getParams('com_rssfactory');

$logger = null;

if ('cronjob' === $type && $configuration->get('cron.log', 0)) {
    $logger = new Katzgrau\KLogger\Logger(JPATH_ADMINISTRATOR . '/logs/rssfactory/');
    $logger->info('Cron Job started.');
}

switch ($type) {
    case 'pseudocron':
        $html[] = '<!DOCTYPE html><html><head><meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT" /></head>';
        $context = 'com_rssfactory.pseudocron.key';
        $session = JFactory::getSession();
        $key = $session->get($context, '');

        $session->clear($context);

        if ($key === $password) {
            $valid = true;
        }
        break;

    default:
    case 'cronjob':
        if ($password == $configuration->get('refresh_password', '')) {
            $valid = true;

            if ($logger) {
                $logger->info('Provided password is valid.');
            }
        }
        else {
            if ($logger) {
                $logger->warning('Provided password is not valid.');
            }
        }
        break;
}

if ($valid) {
    $configuration = JComponentHelper::getParams('com_rssfactory');

    if ('pseudocron' == $type) {
        $configuration->set('pseudocron_last_refresh', JFactory::getDate()->toSql());

        $extension->load($result);
        $extension->params = $configuration->toString();
        $extension->store();
    }

    $memoryLimit = $configuration->get('cron.memory_limit', 128);

    if (-1 !== $memoryLimit) {
        $memoryLimit .= 'M';
    }

    ini_set('memory_limit', $memoryLimit);

    // Initialise variables.
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/loader.php';
    JLoader::register('JRSSFactoryProParser', JPATH_COMPONENT_SITE . '/parsers/parser.php');
    JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models/', 'RssFactoryBackendModel');

    // Get model.
    /** @var RssFactoryBackendModelFeed $model */
    $model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Feed', 'RssFactoryBackendModel', array(
        'logger' => $logger,
    ));

    \Joomla\CMS\Plugin\PluginHelper::importPlugin('system');

    // Refresh all feeds.
    $model->refresh();

    if ($logger) {
        $logger->info('Cron Job ended.');
    }
}

if ('pseudocron' == $type) {
    $html[] = '</html>';
}
elseif ($valid) {
    $refreshed = $model->getState('refreshed', array());

    foreach ($refreshed as $item) {
        echo '#' . $item['feed_id'] . ' (' . $item['feed_url'] . '): ' . $item['stories'] . '<br />';
    }
}

echo implode("\n", $html);
