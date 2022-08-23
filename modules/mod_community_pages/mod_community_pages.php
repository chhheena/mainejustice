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

$model = CFactory::getModel('pages');
$limit = $params->get('limit', 5);
$pageType = $params->get('displaysetting', 0);
$ordering = $params->get('orderingsetting', 'latest');

if ($pageType) {
    //1 = my pages
    if(!CFactory::getUser()->id){
        //since this is my page only and if there is no userid provided, it should be empty
        $tmpPages = array();
    }else{
        // limit the results and set limit start to 0 to prevent conflict with pagination
        $model->setState('limit', $limit);
        $model->setState('limitstart', 0);

        // my pages with filtered category
        if ($params->get('filter_by', 0) == 2 && $params->get('jspagecategory', 0) > 0) {
            $tmpPages = $model->getPages(CFactory::getUser()->id, $ordering, null, $params->get('jspagecategory', 0));
        } else {
            $tmpPages = $model->getPages(CFactory::getUser()->id, $ordering);
        }
    }
} else {
    //filtered by category
    if ($params->get('filter_by', 0) == 2 && $params->get('jspagecategory', 0) > 0) {
        $tmpPages = $model->getAllPages($params->get('jspagecategory', 0), $ordering, null, null, true, false, false, true);
    } else {
        //0 = show all pages
        $tmpPages = $model->getAllPages(null, $ordering, null, null, true, false, false, true);
    }
}

$pages = array();
$data = array();

//1 = featured only
if ($params->get('filter_by', 0) == 1) $featuredOnly = true;
else $featuredOnly = false;

if ($featuredOnly) {
    $featured = new CFeatured(FEATURED_PAGES, $limit);
    $featuredPages = $featured->getItemIds();
}

foreach ($tmpPages as $row) {
    //if we only show featured item, and the item does not exists.
    if ($featuredOnly && !in_array($row->id, $featuredPages)) {
        continue;
    }

    $page = JTable::getInstance('Page', 'CTable');
    $page->bind($row);
    $page->description = JHTML::_('string.truncate', $page->description, 30);
    $pages[] = $page;
}

$pages = array_slice($pages, 0, $limit);

require(JModuleHelper::getLayoutPath('mod_community_pages', $params->get('layout', 'default')));