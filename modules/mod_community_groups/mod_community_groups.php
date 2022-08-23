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

$model = CFactory::getModel('groups');
$limit = $params->get('limit', 5);
$groupType = $params->get('displaysetting', 0);

if ($groupType) {
    //1 = my groups
    if(!CFactory::getUser()->id){
        //since this is my group only and if there is no userid provided, it should be empty
        $tmpGroups = array();
    }else{
        // limit the results and set limit start to 0 to prevent conflict with pagination
        $model->setState('limit', $limit);
        $model->setState('limitstart', 0);

        // my groups with filtered category
        if ($params->get('filter_by', 0) == 2 && $params->get('jsgroupcategory', 0) > 0) {
            $tmpGroups = $model->getGroups(CFactory::getUser()->id, null, null, $params->get('jsgroupcategory', 0));
        } else {
            $tmpGroups = $model->getGroups(CFactory::getUser()->id);
        }
    }
} else {
    //filtered by category
    if ($params->get('filter_by', 0) == 2 && $params->get('jsgroupcategory', 0) > 0) {
        $tmpGroups = $model->getAllGroups($params->get('jsgroupcategory', 0), null, null, null, true, false, false, true);
    } else {
        //0 = show all groups
        $tmpGroups = $model->getAllGroups(null, null, null, null, true, false, false, true);
    }
}

$groups = array();
$data = array();

//1 = featured only
if ($params->get('filter_by', 0) == 1) $featuredOnly = true;
else $featuredOnly = false;

if ($featuredOnly) {
    $featured = new CFeatured(FEATURED_GROUPS, $limit);
    $featuredGroups = $featured->getItemIds();
}

foreach ($tmpGroups as $row) {
    //if we only show featured item, and the item does not exists.
    if ($featuredOnly && !in_array($row->id, $featuredGroups)) {
        continue;
    }

    $group = JTable::getInstance('Group', 'CTable');
    $group->bind($row);
    $group->description = JHTML::_('string.truncate', $group->description, 30);
    $groups[] = $group;
}

$groups = array_slice($groups, 0, $limit);

require(JModuleHelper::getLayoutPath('mod_community_groups', $params->get('layout', 'default')));