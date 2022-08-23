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

// Register helpers and classes.
JLoader::register('RssFactoryHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rssfactory.php');
JLoader::register('RssFactoryFeedsHelper', JPATH_COMPONENT_SITE . '/helpers/rssfactoryfeeds.php');
JLoader::register('RssFactoryCache', JPATH_COMPONENT_SITE . '/helpers/rssfactorycache.php');

JLoader::discover('Factory', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/factory');
JLoader::register('RFProHelper', JPATH_COMPONENT_SITE . '/helpers/helper.php');
JLoader::register('FactoryModelList', JPATH_COMPONENT_ADMINISTRATOR . '/models/modellist.php');
JLoader::register('JRSSFactoryProParser', JPATH_COMPONENT_SITE . '/parsers/parser.php');
JLoader::register('RssFactoryFilterHelper', JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/filter.php');

// Define some constants.
define('RSS_FACTORY_COMPONENT_NAME', 'rssfactory');
define('RSS_FACTORY_COMPONENT_PATH', JPATH_ROOT . DS . 'components' . DS . 'com_' . RSS_FACTORY_COMPONENT_NAME);
define('RSS_FACTORY_COMPONENT_ADMIN_PATH', JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_' . RSS_FACTORY_COMPONENT_NAME);
define('RSS_FACTORY_COMPONENT_URI', JURI::root() . 'components/com_' . RSS_FACTORY_COMPONENT_NAME . '/');
define('RSS_FACTORY_COMPONENT_ADMIN_URI', JURI::root() . 'administrator/components/com_' . RSS_FACTORY_COMPONENT_NAME . '/');
define('RSS_FACTORY_XAJAX_PATH', RSS_FACTORY_COMPONENT_PATH . DS . 'xajax');
define('RSS_FACTORY_CLASSNAME', 'RFPROController');
define('RSS_FACTORY_ADMIN_CLASSNAME', 'RFPROAdminController');
define('RSS_FACTORY_TMP_PATH', JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_' . RSS_FACTORY_COMPONENT_NAME . DS . 'tmp');
define('RSS_FACTORY_LAYOUTS_PATH', RSS_FACTORY_COMPONENT_PATH . DS . 'layouts');
define('RSS_FACTORY_SITE_SAFE_MODE_ON', false);
