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

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$tree = modRssFactoryCategoriesHelper::getTree();

JHtml::script('components/com_rssfactory/assets/js/jquery.treeview/jquery.treeview.js');
JHtml::script('components/com_rssfactory/assets/js/jquery.cookie.js');
JHtml::stylesheet('components/com_rssfactory/assets/js/jquery.treeview/jquery.treeview.css');

require JModuleHelper::getLayoutPath('mod_rssfactory_categories', $params->get('layout', 'default'));
