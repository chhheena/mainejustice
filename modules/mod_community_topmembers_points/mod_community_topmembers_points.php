<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

// Check if JomSocial core file exists
$corefile   = JPATH_ROOT . '/components/com_community/libraries/core.php';

jimport('joomla.filesystem.file');

if (!JFile::exists($corefile)) {
    return;
}

// Include JomSocial's Core file, helpers, settings...
require_once($corefile);
require_once dirname(__FILE__) . '/helper.php';

$config = CFactory::getConfig();
$enablekarma = 0;

if ($config->get('enablekarma')) {
    $enablekarma = $params->get('show_karma', 1);
}

$params->def('enablekarma', $enablekarma);
$modTopMembersHelper = new modCommunityTopmembersPointsHelper();
$users = $modTopMembersHelper->getMembersData($params);

// Add proper stylesheet
JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

// preload users
$CFactoryMethod = get_class_methods('CFactory');
if (in_array('loadUsers', $CFactoryMethod)) {
    $uids = array();
    foreach ($users as $m) {
        $uids[] = $m->id;
    }
    CFactory::loadUsers($uids);
}

require(JModuleHelper::getLayoutPath('mod_community_topmembers_points', $params->get('layout', 'default')));
