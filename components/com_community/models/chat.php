<?php

/**
 * @copyright (C) 2016 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT . '/components/com_community/models/models.php');

class CommunityModelChat extends JCCModel implements CNotificationsInterface
{
    const FETCHMORE = 1;
    const OPENED = 2;

    function getTotalNotifications($user)
    {
        $config = CFactory::getConfig();
        $enablepm = $config->get('enablepm');

        if (!$enablepm) {
            return;
        }

        $chat = $this->getMyChatList();
        $count = 0;

        foreach ($chat as $item) {
            if ($item->seen == 0 && $item->mute == 0) {
                $count++;
            }
        }
        return $count;
    }

    public function getLastChat($chatId, $offset = 0,  $limit = 20, $seen = 1 )
    {
        if ($seen) {
            $this->seen($chatId);
        }
        
        $my = CFactory::getUser();
        $db = JFactory::getDbo();
        $offset_query = $offset ? " AND a.`id` < ". (int) $offset : '';
        $query = "SELECT a.`id`, a.`chat_id`, a.`user_id`, a.`action`, a.`content`, a.`params`, a.`created_at`
                FROM `#__community_chat_activity` AS a
                INNER JOIN `#__community_chat_participants` AS b ON a.`chat_id` = b.`chat_id`
                WHERE a.`chat_id` = $chatId 
                AND a.`action` IN ('sent', 'leave', 'add', 'change_chat_name') 
                AND b.`user_id` = $my->id
                AND b.`enabled` = 1
                $offset_query 
                ORDER BY a.`id` DESC LIMIT $limit";

        $db->setQuery($query);
        $list = $db->loadObjectList();
        $count = count($list);

        $result = new stdClass();
        $result->seen = array();
        $result->messages = array();

        if ($count) {
            $last_message = $list[0];
            $result->seen = $this->getLastSeen($last_message);
            $result->messages = $this->formatResults($list);
        }

        return $result;
    }

    public function getLastSeen($last_message)
    {
        $query = "SELECT * FROM `#__community_chat_activity`
            WHERE chat_id = " . $last_message->chat_id . "
            AND action = 'seen'
            AND id > " . $last_message->id;

        $db = JFactory::getDbo();
        $list = $db->setQuery($query)->loadObjectList();

        if (count($list)) {
            
            array_map( function( $item ) {
                $item->created_at = strtotime( $item->created_at );
            }, $list);

            return $list;
        } else {
            return array();
        }
    }

    public function getChatList($ids = array())
    {
        $data = new stdClass();
        $list = array();

        foreach ($ids as $id) {
            $chat = $this->getChat($id);
            if ($chat) {
                $chat->seen = $this->isSeen($chat);

                if ($chat->type === 'single') {
                    $partner = $this->getChatPartner($chat->chat_id);
                    $chat->partner = $partner[0];
                }

                $list[] = $chat;
            }
        }

        $data->list = $list;
        $data->buddies = $this->getBuddies($ids);

        return $data;
    }

    public function getChat($id)
    {
        $db = JFactory::getDbo();
        $my = CFactory::getUser();

        $query = "SELECT a.`name`, a.`type`, b.`chat_id`, b.`enabled`, b.`mute`, a.`last_msg`
            FROM `#__community_chat` a
            INNER JOIN `#__community_chat_participants` b ON a.`id` = b.`chat_id`
            WHERE b.`chat_id` = $id
            AND b.`user_id` = $my->id";

        $chat = $db->setQuery($query)->loadObject();
        $chat->blocked = false;

        $query = "SELECT user_id
            FROM `#__community_chat_participants`
            WHERE chat_id = $chat->chat_id AND user_id != $my->id AND enabled = 1";
        $user_ids = $db->setQuery($query)->loadColumn();

        if ($chat->type === 'single') {
            $user = CFactory::getUser($user_ids[0]);
            $chat->blocked = $user->isBlocked() || $this->isBlockWith($my->id, $user->id) || $this->isBlockWith($user->id, $my->id);
            $chat->name = $user->getDisplayName();
            $chat->thumb = $this->getThumbAvatar($user);
            $chat->users = $user_ids;
        } else if ($chat->type === 'group') {
            $chat->name = $chat->name ? htmlspecialchars($chat->name) : array();
            $chat->users = array();
            $chat->participants = 0;
            foreach ($user_ids as $index => $user_id) {
                $user = CFactory::getUser($user_id);
                if (is_array($chat->name)) {
                    $chat->name[] = $user->getDisplayName();
                }
                $chat->participants++;
                $chat->users[] = $user_id;
            }
            $chat->thumb = JUri::root() . 'components/com_community/assets/group_thumb.jpg';
        }

        return $chat;
    }

    function isBlockWith($my_id, $userid) {
        $blockModel = CFactory::getModel('block');
        return $blockModel->getBlockStatus($my_id, $userid, 'block');
    }

    public function formatResults($chatList)
    {
        foreach ($chatList as $chat) {
            $chat->content = htmlspecialchars($chat->content);
            $chat->created_at = strtotime($chat->created_at);
            $params = json_decode($chat->params);
            $attachment = NULL;

            if (isset($params->attachment) && isset($params->attachment->type)) {
                if ($params->attachment->type === 'image') {
                    $attachment = $params->attachment;

                    // Fix legacy image attachment which is saved as string.
                    if (isset($attachment->thumburl)) {
                        $attachment->url = JURI::root() . $attachment->thumburl;
                    } else if (isset($attachment->id)) {
                        $photoTable = JTable::getInstance('Photo', 'CTable');
                        $photoTable->load($attachment->id);

                        $attachment->url = $photoTable->getThumbURI();
                    }

                } else if ($params->attachment->type === 'file') {
                    $attachment = $params->attachment;

                    // Fix legacy image attachment which is saved as string.
                    if (isset($attachment->path)) {
                        $attachment->url = JURI::root() . $attachment->path;
                    } else if (isset($attachment->id)) {
                        $fileTable = JTable::getInstance('File', 'CTable');
                        $fileTable->load($attachment->id);

                        $attachment->name = $fileTable->name;
                        $attachment->url = JURI::root() . $fileTable->filepath;
                    }

                } else {
                    $attachment = $params->attachment;
                    $attachment->description = CStringHelper::trim_words($attachment->description);
                }
            }

            $chat->attachment = $attachment ? json_encode($attachment) : '{}';
        }

        return $chatList;
    }

    public function getSingleChatByUser($user_id)
    {
        $user_id = (int) $user_id;
        $data = new stdClass();
        $my = CFactory::getUser();
        $query = "SELECT a.id
            FROM `#__community_chat` AS a
            INNER JOIN `#__community_chat_participants` AS b ON a.id = b.chat_id
            INNER JOIN `#__community_chat_participants` AS c ON a.id = c.chat_id
            WHERE a.`type` = 'single' AND b.user_id = $my->id AND c.user_id = $user_id AND b.enabled = 1 AND c.enabled = 1";

        $db = JFactory::getDbo();
        $db->setQuery($query);
        $chat_id = $db->loadResult();

        if ($chat_id) {
            $data = $this->getLastChat($chat_id);
            $data->chat_id = $chat_id;
        }

        $user = CFactory::getUser($user_id);
        $ob = new stdClass();
        $ob->id = $user->id;
        $ob->name = $user->name;
        $ob->avatar = $this->getThumbAvatar($user);
        $data->partner = $ob;

        return $data;
    }

    public function initializeChatData($existed = array(), $opened = array() )
    {
        $data = new stdClass();
        $data->list = false;
        $data->buddies = false;
        $config = CFactory::getConfig();
        
        $limit = (int) $config->get('message_sidebar_softlimit', 15);
        
        $list = $this->getMyChatList($existed, $limit, self::FETCHMORE);
        $opened = $this->getMyChatList($opened, false, self::OPENED);

        if (count( (array) $list)) {
            $chatids = array();

            foreach ($list as &$item) {
                $chatids[] = $item->chat_id;
                if (!$item->enabled) {
                    unset($item);
                    continue;
                }
                unset($item->enabled);
            }

            foreach ( $opened as &$o ) {
                if ( !in_array( $o->chat_id, $chatids ) ) {
                    $chatids[] = $o->chat_id;
                    if ( !$o->enabled ) {
                        unset($o);
                        continue;
                    }
                    unset($o->enabled);
                }
            }

            $data->list = $list;
            $data->opened = $opened;
            $data->buddies = $this->getBuddies($chatids);
            // only get last_activity on first load
            if (!count($existed)) {
                $data->last_activity = $this->getLastActivity();
            }
        }

        return $data;
    }

    public function getBuddies($chatids)
    {
        $db = JFactory::getDbo();
        $my = CFactory::getUser();
        $query = "SELECT user_id
            FROM `#__community_chat_participants`
            WHERE chat_id IN (". implode(',', $chatids).")
            GROUP BY user_id";

        $ids = $db->setQuery($query)->loadColumn();

        $buddies = new stdClass();

        foreach ($ids as $id) {
            $profile = CFactory::getUser($id);
            $buddy = new stdClass();
            $buddy->id = $id;
            $buddy->name = $my->id == $id ? JText::_('COM_COMMUNITY_CHAT_YOU') : $profile->getDisplayName(false, true);
            $buddy->online = $profile->isOnline();
            $buddy->blocked = $profile->isBlocked();
            $buddy->profile_link = $profile->getProfileLink();
            $buddy->avatar = $this->getThumbAvatar($profile);
            $buddies-> { $id } = $buddy;
        }

        return $buddies;
    }

    public function getThumbAvatar($profile) {
        return $profile->isBlocked() ? JUri::root() . 'components/com_community/assets/user-Male-thumb.png' : $profile->getThumbAvatar();
    }

    public function getMyChatList($ids = array(), $limit = false, $mode = false, $userId = null)
    {   
        // userId param is using by cronjob to check other users chat list
        if ($userId) {
            $my = CFactory::getUser($userId);
        } else {
            $my = CFactory::getUser();
        }

        $db = JFactory::getDbo();

        $query = "SELECT c.id as chat_id, c.type, c.name, c.last_msg, cp.enabled, cp.mute
            FROM `#__community_chat` c
            INNER JOIN `#__community_chat_participants` cp ON c.id = cp.chat_id
            WHERE cp.user_id = $my->id AND cp.enabled = 1";
        
        if ($mode == self::OPENED) {
            if ( count($ids) ) {
                $query .= " AND c.id IN (" .implode(',', $ids). ")" ;
            } else {
                return new stdClass;
            }
            
        } else if ( $mode == self::FETCHMORE ) {
            $query .= count($ids) ? " AND c.id NOT IN (" .implode(',', $ids). ")" : "";
        }

        $query .= ' ORDER BY c.last_msg DESC';
        
        if ($limit) {
            $query .= " LIMIT ". $limit;
        }
        $list = $db->setQuery($query)->loadObjectList();

        $chat = new stdClass();

        foreach ($list as $item) {
            $isSeen = $this->isSeen($item, $userId);
            if ($isSeen) {
                $item->seen = 1;
            } else {
                $item->seen = 0;
            }
            unset($item->last_msg);

            $query = "SELECT user_id
                FROM `#__community_chat_participants`
                WHERE chat_id = $item->chat_id AND user_id != $my->id AND enabled = 1";

            if ($item->type === 'single') {
                $user_id = $db->setQuery($query)->loadResult();
                $user = CFactory::getUser($user_id);
                $item->name = $user->getDisplayName(false, true);
                $item->thumb = $this->getThumbAvatar($user);
                $item->online = $user->isOnline();
                $item->blocked = $user->isBlocked() || $this->isBlockWith($my->id, $user_id) || $this->isBlockWith($user_id, $my->id);
                $item->users = array( (int) $my->id, (int) $user->id );
            } else if ($item->type === 'group' ) {
                $user_ids = $db->setQuery($query)->loadColumn();
                $item->name = $item->name ? htmlspecialchars($item->name) : array();
                $item->participants = 0;
                $item->blocked = false;
                $item->users = array( (int) $my->id );
                foreach ($user_ids as $user_id) {
                    $user = CFactory::getUser($user_id);

                    if (is_array($item->name)) {
                        $item->name[] = $user->getDisplayName(false, true);
                    }

                    $item->users[] = (int) $user->id;
                    $item->participants++;
                }
                $item->thumb = JUri::root() . 'components/com_community/assets/group_thumb.jpg';
            }

            $chat->{ 'chat_' . $item->chat_id } = $item;
        }
        return $chat;
    }

    public function isSeen($item, $userId = null)
    {   
        // userId param is using by cronjob to check other users chat list
        if ($userId) {
            $my = CFactory::getUser($userId);
        } else {
            $my = CFactory::getUser();
        }

        $db = JFactory::getDbo();

        $query = "SELECT id
            FROM `#__community_chat_activity`
            WHERE chat_id = " . $item->chat_id . "
            AND user_id = " . $my->id . "
            ORDER BY id DESC LIMIT 1 ";

        $action = $db->setQuery($query)->loadResult();

        if ($action >= $item->last_msg) {
            return true;
        } else {
            return false;
        }
    }

    public function getChatPartner($chatid)
    {
        $user = CFactory::getUser();
        $db = JFactory::getDbo();

        $query = "SELECT cu.userid
                FROM `#__community_users` as cu
                INNER JOIN `#__community_chat_participants` as ccp on cu.userid = ccp.user_id
                WHERE ccp.chat_id = " . $chatid . "
                AND ccp.user_id != " . $user->id;

        $parter = $db->setQuery($query)->loadColumn();

        return $parter;
    }

    public function getActivity($last_activity = 0)
    {
        $my = CFactory::getUser();

        if (!$my->id) {
            return false;
        }

        $db = JFactory::getDbo();

        $query = "SELECT COUNT(ca.id)
            FROM `#__community_chat_activity` ca
            INNER JOIN `#__community_chat_participants` cc ON ca.chat_id = cc.chat_id
            WHERE cc.user_id = ".$my->id."
            AND cc.enabled = 1
            AND ca.id > " . $last_activity;

        $db->setQuery($query);
        $count = $db->loadResult();

        if ($count) {
            $query = "SELECT ca.*
                FROM `#__community_chat_activity` ca
                INNER JOIN `#__community_chat_participants` cc ON ca.chat_id = cc.chat_id
                INNER JOIN `#__community_chat` c ON c.id = ca.chat_id
                WHERE cc.user_id = ".$my->id."
                AND cc.enabled = 1
                AND ca.id > " . $last_activity;

            $db->setQuery($query);
            $list = $db->loadObjectList();

            $activities = $this->formatResults($list);
            $newcomer = array();
            foreach ($activities as $a) {
                if ($a->action === 'add') {
                    $profile = CFactory::getUser($a->user_id);
                    $user = new stdClass();
                    $user->id = $profile->id;
                    $user->name = $profile->name;
                    $user->avatar = $this->getThumbAvatar($profile);
                    $user->blocked = $profile->isBlocked();
                    $newcomer[] = $user;
                }
            }

            $result = new stdClass();
            $result->activities = $activities;
            $result->newcomer = $newcomer;

            return $result;
        } else {
            return new stdClass();
        }
    }

    public function getLastActivity()
    {
        $my = CFactory::getUser();
        $db = JFactory::getDbo();

        $query = "SELECT ca.*
                FROM `#__community_chat_activity` ca
                INNER JOIN `#__community_chat_participants` cc ON ca.chat_id = cc.chat_id
                WHERE cc.user_id = ".$my->id."
                AND cc.enabled = 1
                ORDER BY ca.id DESC LIMIT 1";

        $id = $db->setQuery($query)->loadResult();

        return $id ? $id : 0;
    }

    public function addActivity($chatid, $user_id, $action, $content = '', $params = '', $created_at = '')
    {
        $table = JTable::getInstance('ChatActivity', 'CTable');
        $data = array(
            'chat_id' => $chatid,
            'user_id' => $user_id,
            'action' => $action,
            'content' => $content,
            'params' => $params,
            'created_at' => $created_at ? $created_at : gmdate('Y-m-d H:i:s')
        );

        $table->bind($data);
        $table->store();

        return $table;
    }

    public function recallMessage($chatReplyId)
    {
        $my = CFactory::getUser();

        // simple and straight forward validation
        $timeout = CFactory::getConfig()->get('message_recall_minutes');
        if (!$timeout) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = "SELECT id FROM " . $db->quoteName('#__community_chat_activity') . " WHERE "
            . $db->quoteName('id') . "=" . $db->quote($chatReplyId) . " AND "
            . $db->quoteName('user_id') . "=" . $db->quote($my->id);
        $db->setQuery($query);

        $result = $db->loadColumn();
        if ($result) { // if there exists such record, delete it immediately
            $query = "DELETE FROM " . $db->quoteName('#__community_chat_activity') . " WHERE "
                . $db->quoteName('id') . "=" . $db->quote($chatReplyId) . " AND "
                . $db->quoteName('user_id') . "=" . $db->quote($my->id);
            $db->setQuery($query);
            return $db->execute();
        }

        return false;
    }

    public function addChat($chatid, $message, $attachment)
    {
        if ($this->isReachLimit()) {
            $error = new stdClass;
            $error->error = JText::_('COM_COMMUNITY_PM_LIMIT_REACHED');
            return $error;
        }
        
        $chat = $this->getChat($chatid);
        if ($chat->blocked) {
            return '';
        }

        $my = CFactory::getUser();
        $params = new CParameter();
        $params->set('attachment', $attachment);
        $params = $params->toString();

        $activity = $this->addActivity($chatid, $my->id, 'sent', $message, $params, gmdate('Y-m-d H:i:s'));
        $this->updateLastChat($chatid, $activity->id);

        $data = new stdClass();
        $data->chat_id = $activity->chat_id;
        $data->reply_id = $activity->id;
        $data->attachment = $attachment;

        if (isset($attachment->type)) {
            // set photo status to ready
            if ($attachment->type == 'image' && $attachment->id > 0) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($attachment->id);
                $photo->status = 'ready';
                $photo->store();
            }
        }

        return $data;
    }

    function isReachLimit() {
        $config = CFactory::getConfig();
        $pmperday = $config->get('pmperday');
        $offset = $this->getTimeOffset();
        
        $user = JFactory::getUser();
        $utz = $user->getTimezone();
        $startTime = strtotime(JHTml::date('now', 'Y-m-d 00:00:00')) - $offset;
        $endTime = strtotime(JHTml::date('now', 'Y-m-d 23:59:59')) - $offset;
        $start = gmdate('Y-m-d H:i:s', $startTime);
        $end = gmdate('Y-m-d H:i:s', $endTime);
       
        $db = JFactory::getDbo();
        $query = 'SELECT COUNT(`id`)
            FROM `#__community_chat_activity`
            WHERE user_id = '. $user->id .'
            AND `action` = '.$db->quote('sent').'
            AND `created_at` >= ' . $db->quote($start) . '
            AND `created_at` <= '. $db->quote($end);

        $db->setQuery($query);
        $count = $db->loadResult();
        
        return $pmperday > $count ? false : true;
    }
    
    function getTimeOffset() {
        $utz = JFactory::getUser()->getTimezone();
        $tz = date_default_timezone_get();
        date_default_timezone_set($utz->getName());
        $offset = date('Z');
        date_default_timezone_set($tz);
        return $offset;
    }

    function updateLastChat($chatid, $activity_id)
    {
        $query = 'UPDATE `#__community_chat` SET last_msg = "' . $activity_id . '" WHERE id = ' . $chatid;
        $db = JFactory::getDbo();
        $db->setQuery($query)->execute();
    }

    public function createChat($message, $attachment, $partner, $name)
    {
        if ($this->isReachLimit()) {
            $error = new stdClass;
            $error->error = JText::_('COM_COMMUNITY_PM_LIMIT_REACHED');
            return $error;
        }
        
        $chatTable = JTable::getInstance('Chat', 'CTable');
        $my = CFactory::getUser();
        $partner = json_decode($partner);
        $count = count($partner);

        $chat = new stdClass();

        if ($count === 1) {
            $chat->type = 'single';
            $chat->name = '';
            $chat->partner = $partner[0];
        } elseif ($count > 1) {
            $chat->type = 'group';
            $chat->name = '';
            $chat->participants = $count;
        } else {
            return;
        }

        $chatTable->bind($chat);

        if (!$chatTable->store()) {
            return;
        }

        $chatid = $chatTable->id;
        $ids = $partner;
        $ids[] = $my->id;

        foreach ($ids as $id) {
            $data = new stdClass();
            $data->chat_id = $chatid;
            $data->user_id = $id;

            $db = JFactory::getDbo();
            $insert = $db->insertObject('#__community_chat_participants', $data, 'id');

            if (!$insert) {
                return;
            }
        }

        $chat->chat_id = $chatid;
        $result = $this->addChat($chatid, $message, $attachment);
        $result->chat = $chat;

        if (isset($attachment->type)) {
            // set photo status to ready
            if ($attachment->type == 'image' && $attachment->id > 0) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($attachment->id);
                $photo->status = 'ready';
                $photo->store();
            }
        }

        return $result;
    }

    public function seen($chat_id)
    {
        $my = CFactory::getUser();
        $last_user_activity = $this->getLastUserActivity($chat_id);
        $last_chat = $this->getLastChatTime($chat_id);

        // if ($last_user_activity < $last_chat) {
            $this->deleteOldSeen($chat_id);
            $this->addActivity($chat_id, $my->id, 'seen');
        // }
    }

    public function isLastActive($chat_id, $user_id)
    {
        $query = 'SELECT user_id FROM `#__community_chat_activity` WHERE chat_id ='.$chat_id.' ORDER BY id DESC LIMIT 1';

        $db = JFactory::getDbo();
        $result = $db->setQuery($query)->loadResult();

        return $result == $user_id ? true : false;
    }

    public function deleteOldSeen($chat_id)
    {
        $my = CFactory::getUser();

        $query = 'DELETE FROM `#__community_chat_activity` '
            . 'WHERE action="seen" '
            . 'AND user_id = ' . $my->id . ' '
            . 'AND chat_id = ' . $chat_id;

        $db = JFactory::getDbo();
        $db->setQuery($query)->execute();
    }

    public function getLastChatTime($chat_id)
    {
        $query = 'SELECT id FROM `#__community_chat_activity` '
            . 'WHERE chat_id = ' . $chat_id . ' '
            . 'AND action in ("sent", "leave", "add") '
            . 'ORDER BY id '
            . 'DESC LIMIT 1';

        $db = JFactory::getDbo();

        return $db->setQuery($query)->loadResult();
    }

    public function getLastUserActivity($chat_id)
    {
        $my = CFactory::getUser();

        $query = 'SELECT id FROM `#__community_chat_activity` '
            . 'WHERE user_id = ' . $my->id . ' '
            . 'AND chat_id = ' . $chat_id . ' '
            . 'ORDER BY id '
            . 'DESC LIMIT 1';

        $db = JFactory::getDbo();

        return $db->setQuery($query)->loadResult();
    }

    public function addPrivateMessage($to, $msg, $attachment)
    {
        $chat_id = $this->getPrivateChatByUser($to);

        if ($chat_id) {
            return $this->addChat($chat_id, $msg, $attachment);
        } else {
            return $this->createChat($msg, $attachment, json_encode(array($to)), '');
        }
    }

    public function getPrivateChatByUser($userid)
    {
        $my = CFactory::getUser();
        $db = JFactory::getDbo();

        $query = "SELECT a.id
        FROM `#__community_chat` AS a
        INNER JOIN `#__community_chat_participants` AS b ON a.id = b.chat_id
        INNER JOIN `#__community_chat_participants` AS c ON a.id = c.chat_id
        WHERE a.`type` = 'single' AND b.user_id = $my->id AND c.user_id = $userid AND b.enabled = 1 AND c.enabled = 1";

        return $db->setQuery($query)->loadResult();
    }

    public function leaveChat($chat_id)
    {
        $my = CFactory::getUser();

        $db = JFactory::getDbo();
        $query = "UPDATE `#__community_chat_participants` SET enabled = 0 WHERE chat_id = ".$chat_id . " AND user_id =".$my->id;
        $db->setQuery($query)->execute();

        $this->addActivity($chat_id, $my->id, 'leave');
    }

    public function leaveGroupChat($chat_id)
    {
        $my = CFactory::getUser();
        $isGroupChat = $this->isGroupChat($chat_id);

        if ($isGroupChat) {
            $db = JFactory::getDbo();
            $query = "UPDATE `#__community_chat_participants` SET enabled = 0 WHERE chat_id = ".$chat_id . " AND user_id =".$my->id;
            $db->setQuery($query)->execute();

            $this->addActivity($chat_id, $my->id, 'leave');
        }
    }

    public function isGroupChat($chat_id)
    {
        $db = JFactory::getDbo();
        $query = "SELECT `type` FROM `#__community_chat` WHERE id = " . $chat_id;
        $result = $db->setQuery($query)->loadResult();

        if ($result && $result == 'group') {
            return true;
        }

        return false;
    }

    public function addPeople($chat_id, $ids)
    {
        $db = JFactory::getDbo();
        $query = "SELECT type FROM `#__community_chat` WHERE id =" .$chat_id;
        $type = $db->setQuery($query)->loadResult();

        if ($type != 'group') {
            return;
        }

        $query = "SELECT user_id FROM `#__community_chat_participants` "
            . "WHERE chat_id = ".$chat_id." "
            . "AND user_id in (". implode(',', $ids) .") "
            . "AND enabled = 0";

        $exist_user = $db->setQuery($query)->loadColumn();

        if (count($exist_user)) {
            foreach ($exist_user as $uid) {
                $q = "UPDATE `#__community_chat_participants` SET enabled = 1 WHERE chat_id = ".$chat_id ." AND user_id = ". $uid;

                $db->setQuery($q)->execute();

                $this->addActivity($chat_id, $uid, 'add');
            }
        }

        $query = "SELECT user_id FROM `#__community_chat_participants` WHERE chat_id =".$chat_id;
        $user_ids = $db->setQuery($query)->loadColumn();
        $id_diff = array_diff($ids, $user_ids);

        if (count($id_diff)) {
            $query = "UPDATE `#__community_chat` SET type = 'group' WHERE id = " . $chat_id;
            $db->setQuery($query)->execute();

            foreach ($id_diff as $uid) {
                $data = new stdClass();
                $data->chat_id = $chat_id;
                $data->user_id = $uid;

                $db->insertObject('#__community_chat_participants', $data, 'id');

                $this->addActivity($chat_id, $uid, 'add');
            }
        }
    }

    public function getFriendListByName($keyword, $exclusion)
    {
        $my = CFactory::getUser();
        $db = $this->getDBO();

        $andName = '';
        $exclude = '';

        // validate exclusion
        if (is_string($exclusion)) {
            $exclusion = explode(',', $exclusion);
            $exclusion = array_map( function($ex) {
                return (int) $ex;
            }, $exclusion);
        }

        if ( count($exclusion) ) {
            $exclude = ' AND b.'.$db->quoteName('id').' not in ('. implode(',', $exclusion).')';
        }

        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');

        if(!empty($keyword)){
            $andName    = ' AND b.' . $db->quoteName( $nameField ) . ' LIKE ' . $db->Quote( '%'.$keyword.'%' ) ;
        }

        $query = 'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
            . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
            . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $my->id )
            . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
            . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
            . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
            . $exclude
            . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
            . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
            . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($my->id)
            . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
            . $andName
            . ' ORDER BY b.' . $db->quoteName($nameField)
            . ' LIMIT 200';

        $db->setQuery( $query );
        $friends = $db->loadColumn();

        return $friends;
    }

    public function muteChat($chat_id, $mute)
    {
        $my = CFactory::getUser();

        $query = "UPDATE `#__community_chat_participants` "
            . "SET mute = " . $mute . " "
            . "WHERE chat_id = ". $chat_id ." "
            . "AND user_id = " . $my->id;

        $db = JFactory::getDbo();
        $db->setQuery($query)->execute();
    }

    public function disableChat($chat_id) {
        $my = CFactory::getUser();

        $query = "UPDATE `#__community_chat_participants` "
            . "SET enabled = 0 "
            . "WHERE chat_id = ". $chat_id ." "
            . "AND user_id = " . $my->id;

        $db = JFactory::getDbo();
        $db->setQuery($query)->execute();
    }

    public function markAllAsRead() {
        $chats = $this->getMyChatList();
        foreach ($chats as $chat) {
            if (!$chat->seen) {
                $this->seen($chat->chat_id);
            }
        }
    }

    public function changeGroupChatName($chat_id, $name) {
        $name = mb_substr($name, 0, 250);
        $my = CFactory::getUser();
        
        $result = new stdClass();
        if ( $name && $this->isGroupChat($chat_id) && $this->isGroupChatMember($my->id, $chat_id)) {
            $db = JFactory::getDbo();
            $query = "UPDATE `#__community_chat` SET `name` = " . $db->quote($name) . " WHERE id = $chat_id";
            $db->setQuery($query);

            try {
                $db->execute();

                $params = new JRegistry();
                $params->set('groupname', $name);
                $this->addActivity($chat_id, $my->id, 'change_chat_name', '', $params->toString());

                $result->success = true;
                $result->groupname = htmlspecialchars($name);
            } catch ( Exception $e) {
                $result->error = 'Unknown error';
            }
        } else {
            $result->error = 'Is not group chat or group chat member or empty name';
        }
        return $result;
    }

    public function isGroupChatMember($user_id, $chat_id) {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(id)
            FROM `#__community_chat_participants`
            WHERE `user_id` = $user_id AND `chat_id` = $chat_id AND `enabled` = 1";
        
        $db->setQuery($query);
        return $db->loadResult() ? true : false;
    }

    public function searchChat($keyword = '', $exclusion = '') {
        $length = mb_strlen(trim($keyword));

        if ($length < 2) {
            $error = new stdClass;
            $error->error = JText::_('COM_COMMUNITY_CHAT_SEARCH_KEY_WORD_LIMIT');
            return $error;
        }

        $keyword = mb_substr($keyword, 0, 50);

        $userid = CFactory::getUser()->id;
       
        // validate exclusion
        $exclusion = explode(',', $exclusion);
        $exclusion = array_map( function($ex) {
            return (int) $ex;
        }, $exclusion);

        $singles = $this->searchSingleChats($userid, $keyword, $exclusion);
        
        $exclusion = array_merge($exclusion, $singles);

        $namedGroup = $this->searchNamedGroups($userid, $keyword, $exclusion);
        
        $exclusion = array_merge($exclusion, $namedGroup);

        $unnamedGroup = $this->searchUnnamedGroups($userid, $keyword, $exclusion);
        
        $results = new stdClass();
        $results->single = array_map( function($chatid) {
            $i = $this->getChat($chatid);
            $i->seen = 1;
            return $i;
        }, $singles) ;

        $groups = array_merge($namedGroup, $unnamedGroup);
        $results->group = array_map( function($chatid) {
            $i = $this->getChat($chatid);
            $i->seen = 1;
            return $i;
        }, $groups);

        return $results;
    }

    public function searchUnnamedGroups( $userid, $keyword = '', $exclusion = array() ) {
        $friends = $this->getFriendListByName($keyword, $exclusion);
        
        if (!count($friends)) {
            return array();
        }

        $andFriend = ' AND c.`user_id` IN ('.implode(',', $friends).')';

        $exclude =count($exclusion) ? ' AND a.`id` NOT IN ('.implode(',', $exclusion).')' : '';

        $db = JFactory::getDbo();
        $query = "SELECT a.`id`, a.`last_msg`
            FROM `#__community_chat` a
            INNER JOIN `#__community_chat_participants` b ON a.`id` = b.`chat_id`
            INNER JOIN `#__community_chat_participants` c ON a.`id` = c.`chat_id`
            WHERE a.`name` = '' 
            AND a.`type` = 'group'
            AND b.`enabled` = 1
            AND b.`user_id` = $userid" . 
            $andFriend .
            $exclude .
            " GROUP BY a.`id` ORDER BY a.`last_msg` ASC";

        try {
            $groups = $db->setQuery($query)->loadColumn();
        } catch( Exception $e ) {
            die('unnamed group error');
        } 
        
        return $groups;
    }

    public function searchNamedGroups( $userid, $keyword = '', $exclusion = array() ) {
        $db = JFactory::getDbo();

        $andKeyword = !empty($keyword) ? ' AND a.`name` LIKE '.$db->quote('%'.$keyword.'%') : '' ;

        $exclude = count($exclusion) ? ' AND a.id NOT IN ('.implode(',', $exclusion).')' : '';

        $query = "SELECT a.`id`, a.`last_msg`
            FROM `#__community_chat` a
            INNER JOIN `#__community_chat_participants` b ON a.`id` = b.`chat_id`
            WHERE  b.`enabled` = 1
            AND a.`type` = 'group'
            AND b.`user_id` = $userid
            AND a.`name` LIKE ".$db->quote('%'.$keyword.'%')
            . $andKeyword
            . $exclude . 
            " ORDER BY a.`last_msg` ASC";

        try {
            $groups = $db->setQuery($query)->loadColumn();
        } catch ( Exception $e ) {
            die('named group error');
        }

        return $groups;
    }

    public function searchSingleChats($userid, $keyword = '', $exclusion = array() ) {
        $db = JFactory::getDbo();
        $config = CFactory::getConfig();

        $nameField = $config->getString('displayname');
        $andKeyword = !empty($keyword) ? ' AND d.' . $db->quoteName( $nameField ) . ' LIKE ' . $db->quote( '%'.$keyword.'%' ) : '' ;

        $exclude = count($exclusion) ? ' AND a.id NOT IN ('.implode(',', $exclusion).')' : '';

        $query = "SELECT a.`id`, a.`last_msg`
            FROM `#__community_chat` a
            INNER JOIN `#__community_chat_participants` b ON a.`id` = b.`chat_id`
            INNER JOIN `#__community_chat_participants` c ON a.`id` = c.`chat_id`
            INNER JOIN `#__users` d on c.`user_id` = d.`id`
            WHERE  b.`enabled` = 1
            AND a.`type` = 'single'
            AND b.`user_id` = $userid
            AND c.`user_id` != $userid "
            . $andKeyword
            . $exclude .
            " ORDER BY a.`last_msg` ASC";

        $db->setQuery($query);
        
        try {
            $chats = $db->loadColumn();
        } catch( Exception $e ) {
            die('single chat error');
        }

        return $chats;
    }
}
