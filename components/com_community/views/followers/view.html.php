<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

if (!class_exists("CommunityViewFollowers")) {

    class CommunityViewFollowers extends CommunityView
    {   
        public function display($data = NULL)
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $followers = CFactory::getModel('followers');
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');
            $filter = $jinput->getWord('filter', 'all');
            
            $rows = $followers->getFollowers($userid, $sorted, true, $filter);

            $isMine = (($userid == $my->id) && ($my->id != 0));

            $this->attachMiniHeaderUser($user->id);
            
            // Display mini header if user is viewing other user's friend
            if ($userid != $my->id) {
                $this->attachMiniHeaderUser($userid);
            }

            $this->addPathway(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id));

            if ($my->id == $userid) {
                $this->addPathway(JText::_('COM_COMMUNITY_FOLLOWERS'));
            } else {
                $this->addPathway(JText::sprintf('COM_COMMUNITY_FRIEND_FOLLOWERS', $user->getDisplayName()));
            }

            // Check if friend is banned
            $blockModel = CFactory::getModel('block');

            $resultRows = array();

            foreach ($rows as $row) {
                $user = CFactory::getUser($row->id);

                $obj = clone($row);
                $obj->profileLink = CUrlHelper::userLink($row->id);
                $obj->isBlocked = $blockModel->getBlockStatus($user->id, $my->id);

                $resultRows[] = $obj;
            }

            unset($rows);
            $pagination = $followers->getPagination();

            $tmpl = new CTemplate();
            $html = $tmpl->set('isMine', $isMine)
                    ->set('userid', $userid)
                    ->setRef('my', $my)
                    ->setRef('followers', $resultRows)
                    ->set('config', CFactory::getConfig())
                    ->set('pagination', $pagination)
                    ->fetch('followers/list');

            echo $html;
        }

        public function following($data = NULL)
        {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();

            $my = CFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $followers = CFactory::getModel('followers');
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');
            $filter = $jinput->getWord('filter', 'all');
            
            $rows = $followers->getFollowing($userid, $sorted, true, $filter);

            $isMine = (($userid == $my->id) && ($my->id != 0));

            $this->attachMiniHeaderUser($user->id);

            $this->addPathway(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id));

            if ($my->id == $userid) {
                $this->addPathway(JText::_('COM_COMMUNITY_FOLLOWING'));
            } else {
                $this->addPathway(JText::sprintf('COM_COMMUNITY_FRIEND_FOLLOWING', $user->getDisplayName()));
            }

            // Check if friend is banned
            $blockModel = CFactory::getModel('block');

            $resultRows = array();

            foreach ($rows as $row) {
                $user = CFactory::getUser($row->id);

                $obj = clone($row);
                $obj->profileLink = CUrlHelper::userLink($row->id);
                $obj->isBlocked = $blockModel->getBlockStatus($user->id, $my->id);

                $resultRows[] = $obj;
            }

            unset($rows);
            $pagination = $followers->getPagination();

            $tmpl = new CTemplate();
            $html = $tmpl->set('isMine', $isMine)
                    ->set('userid', $userid)
                    ->setRef('my', $my)
                    ->setRef('following', $resultRows)
                    ->set('config', CFactory::getConfig())
                    ->set('pagination', $pagination)
                    ->fetch('followers/following');

            echo $html;
        }
    }
}