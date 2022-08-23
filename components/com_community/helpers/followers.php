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

    class CFollowersHelper
    {   
        static public function isFollowing($id1, $id2)
        {
            $db = JFactory::getDBO();

            $sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_follower')
                  .' WHERE ' . $db->quoteName('following') .'=' . $db->Quote($id2) .' AND ' . $db->quoteName('user_id') .'=' . $db->Quote($id1);

            $db->setQuery($sql);
            $result = $db->loadResult();
            
            return $result;
        }

        static public function getUserFollowingDropdown($targetId){
            $my = CFactory::getUser();
            $user = CFactory::getUser($targetId);

            //if user is not logged in, nothing should be displayed at all
            if (!$my->id || $my->id == $targetId) {
                return false;
            }

            $display = new stdClass();
            
            $display->button = "COM_COMMUNITY_FOLLOWING";
            $display->dropdown = 'COM_COMMUNITY_UNFOLLOW';
            $display->dropdownTrigger = "joms.api.followRemove('".$user->id."');";
            $display->buttonTrigger = false;

            $tmpl = new CTemplate();
            return $tmpl
                ->set('options', $display)
                ->fetch('general/following-dropdown');
        }

        static public function getUserFollowerDropdown($targetId){
            $my = CFactory::getUser();
            $user = CFactory::getUser($targetId);

            //if user is not logged in, nothing should be displayed at all
            if (!$my->id || $my->id == $targetId) {
                return false;
            }

            $display = new stdClass();
            
            if (CFactory::getUser()->authorise('community.request', 'friends.' . $user->id)) {
                $display->dropdown = false;
                $display->dropdownTrigger = false;
                $display->button = "COM_COMMUNITY_FOLLOW"; //by default
                $display->buttonTrigger = "joms.api.followAdd('".$user->id."')";
            }

            if (CFollowersHelper::isFollowing($my->id, $targetId)) {
                $display->button = "COM_COMMUNITY_FOLLOWING";
                $display->dropdown = 'COM_COMMUNITY_UNFOLLOW';
                $display->dropdownTrigger = "joms.api.followRemove('".$user->id."');";
                $display->buttonTrigger = false;
            }

            $tmpl = new CTemplate();
            return $tmpl
                ->set('options', $display)
                ->fetch('general/follower-dropdown');
        }

    }