<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

include_once(JPATH_BASE . '/components/com_community/defines.community.php');
require_once(JPATH_BASE . '/components/com_community/libraries/core.php');

//add style css
JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

// add module-specific assets
$document = JFactory::getDocument();

$document->addStyleSheet(JURI::root(true) . '/modules/mod_community_polls/assets/css/style.css');

if (JFactory::getLanguage()->isRTL()) {
	$document->addStyleSheet(JURI::root(true) . '/modules/mod_community_polls/assets/css/style.rtl.css');
}

$model = CFactory::getModel('polls');
$limit = $params->get('limit', 5);
$pollType = $params->get('displaysetting', 0);

// limit the results and set limit start to 0 to prevent conflict with pagination
$model->setState('limit', $limit);
$model->setState('limitstart', 0);

$filterByIds = ($params->get('pollId', 0) > 0) ? $params->get('pollId', 0) : null;
$filterByCat = ($params->get('filter_by', 0) == 1 && $params->get('jspollcategory', 0) > 0) ? $params->get('jspollcategory', 0) : null;

if ($pollType == 1) {
    $polls = $model->getAllPolls($filterByCat, null, null, $limit, false, false, null, null, CFactory::getUser()->id, null, $filterByIds);
} else if ($pollType == 2) {
    $polls = $model->getAllPolls($filterByCat, null, null, $limit, false, false, null, null, null, 'grouppolls', $filterByIds);
} else if ($pollType == 3) {
    $polls = $model->getAllPolls($filterByCat, null, null, $limit, false, false, null, null, null, 'eventpolls', $filterByIds);
} else {
    $polls = $model->getAllPolls($filterByCat, null, null, $limit, false, false, null, null, null, null, $filterByIds);
}

require(JModuleHelper::getLayoutPath('mod_community_polls', $params->get('layout', 'default')));