<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

defined('_JEXEC') or die;

JLoader::register('ModCommunityPagesCategoriesHelper', __DIR__ . '/helper.php');

//add style css
//JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true) . '/modules/mod_community_pages_categories/style.css');

$categories = ModCommunityPagesCategoriesHelper::getList($params);

require JModuleHelper::getLayoutPath('mod_community_pages_categories', $params->get('layout', 'default'));