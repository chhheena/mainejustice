<?php
/*------------------------------------------------------------------------
# mod_sp_tabbed_articles - Tabbed articles module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2015 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
defined ('_JEXEC') or die('resticted aceess');

require_once __DIR__ . '/helper.php';

$catid 		= $params->get('catid');
$limit 		= $params->get('limit', 3);
$columns 	= $params->get('columns', 3);
$ordering 	= $params->get('ordering', 'latest');

JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addScript( JURI::base(true) . '/modules/mod_sp_tabbed_articles/assets/js/sp-tabbed-articles.js' );

$categories = modSpTabbedArticlesHelper::getSubcategories($catid, false);
require JModuleHelper::getLayoutPath('mod_sp_tabbed_articles', $params->get('layout', 'default'));