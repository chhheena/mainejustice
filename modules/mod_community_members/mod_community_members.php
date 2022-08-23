    <?php
    /**
     * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */

    defined('_JEXEC') or die('Restricted access');

    // Check if JomSocial core file exists
    $corefile = JPATH_ROOT . '/components/com_community/libraries/core.php';

    jimport('joomla.filesystem.file');
    if (!JFile::exists($corefile)) {
        return;
    }

    // Include JomSocial's Core file, helpers, settings...
    require_once($corefile);
    require_once dirname(__FILE__) . '/helper.php';

    $config = CFactory::getConfig();

    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    //$showTotalMembers = $params->get('show_total_members');
    $moduleParams = $params;
    $modMembersHelper = new modCommunityMembers();
    $members = $modMembersHelper->getMembersData($params);

    $filter = $params->get('sorting', 0);

    $totalMembers = '';
    if ($params->get('show_total_members', 1)) {
        if ($filter == 2) {
            $totalMembers = ' ('.$modMembersHelper->getAllFeaturedMembers().')';
        } else if ($filter == 3) {
            $totalMembers = ' ('.$modMembersHelper->getAllFriends().')';
        } else if ($filter == 4) {
            $totalMembers = ' ('.$modMembersHelper->getAllOnlineMembers().')';
        } else {
            $totalMembers = ' ('.$modMembersHelper->getAllMembers().')';
        }
    }

    $data = array();
    $avatars_number = $params->get('avatars_number', 0);

    require(JModuleHelper::getLayoutPath('mod_community_members', $params->get('layout', 'default')));
