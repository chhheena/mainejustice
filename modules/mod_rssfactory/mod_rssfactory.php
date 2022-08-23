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

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Include dependencies.
require_once __DIR__ . '/helper.php';

// Load language file.
JFactory::getLanguage()->load('com_rssfactory');

require_once JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/loader.php';

// Register dependencies.
JLoader::register('JHtmlRssFactoryFeeds', JPATH_SITE . '/components/com_rssfactory/helpers/html/rssfactoryfeeds.php');
JLoader::register('RssFactoryHelper', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/rssfactory.php');
JLoader::register('RssFactoryFeedsHelper', JPATH_SITE . '/components/com_rssfactory/helpers/rssfactoryfeeds.php');
JLoader::register('FactoryHtml', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
JLoader::register('FactoryRoute', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
JLoader::register('FactoryText', JPATH_ADMINISTRATOR . '/components/com_rssfactory/views/view.php');
JLoader::register('RssFactoryCache', JPATH_SITE . '/components/com_rssfactory/helpers/rssfactorycache.php');

$results = modRssFactoryHelper::getResults($params);
$config = modRssFactoryHelper::getConfig($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

if (!$results) {
    return '';
}

require JModuleHelper::getLayoutPath('mod_rssfactory', $params->get('layout', 'default'));
