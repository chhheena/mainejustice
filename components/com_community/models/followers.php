<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die ('Restricted access');

require_once (JPATH_ROOT.'/components/com_community/models/models.php');

class CommunityModelFollowers extends JCCModel
{
    var $_data = null;
    var $_pagination;

    public function __construct()
    {
        parent::__construct();
        global $option;
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        // Get pagination request variables
        $limit = ($config->get('pagination') == 0) ? 5 : $config->get('pagination');
        $limitstart = $jinput->request->get('limitstart', 0, 'INT');

        if (empty($limitstart)) {
            $limitstart = $jinput->get->get('start',0,'INT');
        }

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0?(floor($limitstart/$limit)*$limit): 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }
    
    public function getFollowers($userid = null, $sorted = 'latest', $useLimit = true , $filter = 'all')
    {
        $followers = array ();

        // if guest
        if (empty($userid)) {
            return $followers;
        }

        $db = $this->getDBO();
        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');
        
        //get follower and following count
        $followerTable = JTable::getInstance('Follower', 'CTable');
        $total = $followerTable->getFollowingCount($userid);

        $query	= 'SELECT a.* ';
                
        switch ($filter) {
            case 'all':
                $query	.= ' FROM ' . $db->quoteName( '#__community_follower' ) . ' AS a '
                    . ' WHERE a.'. $db->quoteName('following').' = ' . $db->Quote($userid);

                // Appy pagination
                if (empty($this->_pagination)) {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }

                break;
        }

        switch ($sorted) {
            default:
                $query	.= ' ORDER BY a.'. $db->quoteName('id').' DESC';
                break;
        }

        //do not limit the query if this is a search based on names
        if ($useLimit) {
            $query .= " LIMIT {$limitstart}, {$limit} ";
        }

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // preload all users
        $uids = array();
        foreach($result as $m) {
            $uids[] = $m->user_id;
        }

        CFactory::loadUsers($uids);

        for ($i = 0; $i < count($result); $i++) {
            $usr = CFactory::getUser($result[$i]->user_id);
            $followers[] = $usr;
        }

        return $followers;
    }

    public function getFollowing($userid = null, $sorted = 'latest', $useLimit = true , $filter = 'all')
    {
        $following = array ();

        // if guest
        if (empty($userid)) {
            return $following;
        }

        $db = $this->getDBO();
        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');
        
        //get follower and following count
        $followerTable = JTable::getInstance('Follower', 'CTable');
        $total = $followerTable->getFollowingCount($userid);

        $query  = 'SELECT a.* ';
                
        switch ($filter) {
            case 'all':
                $query  .= ' FROM ' . $db->quoteName( '#__community_follower' ) . ' AS a '
                    . ' WHERE a.'. $db->quoteName('user_id').' = ' . $db->Quote($userid);

                // Appy pagination
                if (empty($this->_pagination)) {
                    jimport('joomla.html.pagination');
                    $this->_pagination = new JPagination($total, $limitstart, $limit);
                }

                break;
        }

        switch ($sorted) {
            default:
                $query  .= ' ORDER BY a.'. $db->quoteName('id').' DESC';
                break;
        }

        //do not limit the query if this is a search based on names
        if ($useLimit) {
            $query .= " LIMIT {$limitstart}, {$limit} ";
        }

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // preload all users
        $uids = array();
        foreach($result as $m) {
            $uids[] = $m->following;
        }

        CFactory::loadUsers($uids);

        for ($i = 0; $i < count($result); $i++) {
            $usr = CFactory::getUser($result[$i]->following);
            $following[] = $usr;
        }

        return $following;
    }
    
    public function isFollowing($userid = null, $following = null)
    {
        // if guest
        if (empty($userid)) {
            return $following;
        }

        $db = $this->getDBO();
        $query  = 'SELECT COUNT(a.id) ';
        $query  .= 'FROM ' . $db->quoteName( '#__community_follower' ) . ' AS a '
            . ' WHERE a.'. $db->quoteName('user_id').' = ' . $db->Quote($userid)
            . ' AND a.'. $db->quoteName('following').' = ' . $db->Quote($following);
        
        $db->setQuery($query);
        $count = $db->loadResult();
        
        return $count;
    }
    
    public function addFollowing($userid = null, $following = null)
    {
        $my = CFactory::getUser();
        $db = $this->getDBO();

        if ($my->id == $following) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_FOLLOWING_CANNOT_ADD_SELF'), 'error');
        }

        $date = JDate::getInstance();
        $query  = 'INSERT INTO '. $db->quoteName('#__community_follower')
            .' SET ' . $db->quoteName('user_id').' = '.$db->Quote($userid)
            . ', '. $db->quoteName('following').' = '.$db->Quote($following)
            . ', '. $db->quoteName('created').' = ' . $db->Quote($date->toSql());

        $db->setQuery($query);
        
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }

    public function unFollow($userid = null, $following = null)
    {
        $my = CFactory::getUser();
        $db = $this->getDBO();

        $query  = 'DELETE FROM '. $db->quoteName('#__community_follower')
            .' WHERE ' . $db->quoteName('user_id').' = '.$db->Quote($userid)
            . ' AND '. $db->quoteName('following').' = '.$db->Quote($following);

        $db->setQuery($query);
        
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $this;
    }

    public function &getPagination()
    {
        return $this->_pagination;
    }
}
