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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

class CommunityModelPolls extends JCCModel implements CLimitsInterface
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination	= '';

	/**f
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $total			= '';

	/**
	 * member count data
	 *
	 * @var int
	 **/
	var $membersCount	= array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe	= JFactory::    getApplication();
		$jinput 	= $mainframe->input;
        $config = CFactory::getConfig();

		// Get pagination request variables
 	 	$limit		= ($config->get('pagination') == 0) ? 5 : $config->get('pagination');
	    $limitstart = $jinput->request->get('limitstart', 0);

	    if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('limitstart', 0, 'uint');
 	 	}

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		return $this->_pagination;
	}

	/**
	 * Loads the categories
	 *
	 * @access	public
	 * @returns Array  An array of categories object
	 */
	public function getCategories($catId = COMMUNITY_ALL_CATEGORIES)
	{
		$db = $this->getDBO();
		$result = null;
		$where = '';

		if ($catId !== COMMUNITY_ALL_CATEGORIES && ($catId != 0 || !is_null($catId))) {
			if ($catId === COMMUNITY_NO_PARENT) {
				$where = 'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( COMMUNITY_NO_PARENT ) . ' ';
			} else {
				$where = 'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( $catId ) . ' ';
			}
		}

		$query	= 'SELECT a.*, COUNT(b.'.$db->quoteName('id').') AS count '
			    . ' FROM ' . $db->quoteName('#__community_polls_category') . ' AS a '
			    . ' LEFT JOIN ' . $db->quoteName( '#__community_polls' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('catid')
			    . ' AND b.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
			    . $where
			    . ' GROUP BY a.'.$db->quoteName('id').' ORDER BY a.'.$db->quoteName('name').' ASC';
		
		$db->setQuery($query);
        
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		return $result;
	}

	/**
	 * Return the number of polls cretion count for specific user
	 **/
	public function getGroupsCreationCount($userId)
	{
		if ($userId == 0) return 0;

		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '
				. $db->quoteName( '#__community_polls' ) . ' '
				. 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote( $userId );
		$db->setQuery( $query );

		$count = $db->loadResult();

		return $count;
	}

	/**
	 * Return the number of polls cretion count for specific user
	 **/
	public function getPollsCreationCount($userId)
	{
		if ($userId == 0) return 0;

		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '
				. $db->quoteName('#__community_polls') . ' '
				. 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($userId);
		$db->setQuery( $query );

		$count = $db->loadResult();

		return $count;
	}

	public function getTotalToday($userId)
	{
		$date = JDate::getInstance();
		$db = JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_polls' ) . ' AS a '
				. ' WHERE a.'.$db->quoteName('creator').'=' . $db->Quote($userId)
				. ' AND TO_DAYS(' . $db->Quote( $date->toSql( true ) ) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = '.$db->Quote(0);
		$db->setQuery( $query );

		$count = $db->loadResult();

		return $count;
	}

	public function getAllCategories()
	{
		$db     = $this->getDBO();

		$query  = 'SELECT *
					FROM ' . $db->quoteName('#__community_polls_category');

		$db->setQuery( $query );
		$result = $db->loadObjectList();

		// bind to table
		$data = array();
		foreach($result AS $row) {
			$pollCat = JTable::getInstance('PollCategory', 'CTable');
			$pollCat->bind($row);
			$data[] = $pollCat;
		}

		return $data;
	}

	function getCategoriesCount()
	{
		$db	=  $this->getDBO();

		$query = "SELECT c.id, c.parent, c.name, count(p.id) AS total, c.description
				  FROM " . $db->quoteName('#__community_polls_category') . " AS c
				  LEFT JOIN " . $db->quoteName('#__community_polls'). " AS p ON p.catid = c.id
							AND p." . $db->quoteName('published') . "=" . $db->Quote( '1' ) . "
				  GROUP BY c.id
				  ORDER BY c.name";

		$db->setQuery( $query );
        try {
            $result = $db->loadObjectList('id');
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		return $result;
	}

	public function getCategoryName($categoryId)
	{
		CError::assert($categoryId, '', '!empty', __FILE__ , __LINE__ );
		$db		= $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName('name') . ' '
				. 'FROM ' . $db->quoteName('#__community_polls_category') . ' '
				. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote( $categoryId );
		$db->setQuery( $query );

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		CError::assert( $result , '', '!empty', __FILE__ , __LINE__ );
		return $result;
	}

	public function getAllPolls($categoryId = null, $sorting = null, $search = null, $limit = null, $pagination = true, $nolimit = false, $groupId = null, $eventId = null, $pageId = null, $userId = null, $task = null, $byIds = null)
	{	
		$db	= $this->getDBO();
		$mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (!$task) $task = $jinput->get('task', '');

		$extraSQL = '';
		$pextra = '';

		if (is_null($limit)) {
			$limit = $this->getState('limit');
		}

		$limit = ($limit < 0) ? 0 : $limit;

        if ($pagination) {
            $limitstart = $this->getState('limitstart');
        } else {
            $limitstart = 0;
        }

        if (!is_null($search)) {
			$extraSQL	.= ' AND (a.'.$db->quoteName('title').' LIKE ' . $db->Quote( '%' . $search . '%' ) . ' OR a.'.$db->quoteName('id'). ' = ' . $db->Quote($search) . ') ';
		}

		$mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        if (!$groupId) {
            $groupId = $jinput->getInt('groupid');
        }

        if (!$eventId) {
            $eventId = $jinput->getInt('eventid');
        }	
        
        if (!$pageId) {
            $pageId = $jinput->getInt('pageid');
        }

		if ($groupId) {
			$extraSQL	.= ' AND a.'.$db->quoteName('groupid').' = ' . $db->Quote($groupId);
		}

		if ($eventId) {
			$extraSQL	.= ' AND a.'.$db->quoteName('eventid').' = ' . $db->Quote($eventId);
		}

		if ($pageId) {
			$extraSQL	.= ' AND a.'.$db->quoteName('pageid').' = ' . $db->Quote($pageId);
		}

		if ($userId) {
			$extraSQL	.= ' AND a.'.$db->quoteName('creator').' = ' . $db->Quote($userId);
		}

		if ($task == 'pagepolls') {
			$extraSQL	.= ' AND a.'.$db->quoteName('pageid').' > ' . $db->Quote(0);
		} else if ($task == 'grouppolls') {
			$extraSQL	.= ' AND a.'.$db->quoteName('groupid').' > ' . $db->Quote(0);
		} else if ($task == 'eventpolls') {
			$extraSQL	.= ' AND a.'.$db->quoteName('eventid').' > ' . $db->Quote(0);
		}
		
		if ($byIds) {
			$extraSQL	.= ' AND a.'.$db->quoteName('id').' IN (' . $byIds . ') ';
		}

		$order	='';
		switch ($sorting) {
			case 'alphabetical':
				$order		= ' ORDER BY a.'.$db->quoteName('title').' ASC ';
				break;
			case 'mostdiscussed':
				$order	= ' ORDER BY '.$db->quoteName('discusscount').' DESC ';
				break;
			case 'mostmembers':
				$order	= ' ORDER BY '.$db->quoteName('membercount').' DESC ';
				break;
			default:
				$order	= ' ORDER BY a.'.$db->quoteName('created').' DESC ';
				break;
		}

		if (!is_null($categoryId) && $categoryId != 0) {
            if (is_array($categoryId)) {
                if (count($categoryId) > 0) {
                    $categoryIds = implode(',', $categoryId);
                    $extraSQL .= ' AND a.' . $db->quoteName('catid'). ' IN(' . $categoryIds . ')';
                }
            } else {
                $extraSQL .= ' AND a.'.$db->quoteName('catid').'=' . $db->Quote($categoryId) . ' ';
            }
		}

		// permissions
        $my = CFactory::getUser();
        $permissions = ($my->id == 0) ? 10 : 20;
        $permissions = (CFactory::getUser()->authorise('community.polleditstate', 'com_community') || CFactory::getUser()->authorise('community.polledit', 'com_community') || CFactory::getUser()->authorise('community.polldelete', 'com_community')) ? 40 : $permissions;

        $permissionSQL = '';
        if ($my->id != 0) {
        	$friendmodel = CFactory::getModel('friends');
        	$friends = $friendmodel->getFriendIds($my->id);
        	if (!empty($friends)) {
            	$permissionSQL = ' OR (a.creator IN(' . implode(',', $friends) . ') AND a.permissions = ' . $db->Quote(30) . ') ';
        	}
        }

        $extraSQL .= ' AND ( a.' . $db->quoteName('permissions') . ' <= ' . $db->quote((int) $permissions) . ' ' . $permissionSQL . '  OR ( a.creator=' . $db->Quote($my->id) . ' AND a.permissions <=' . $db->Quote(40) . ' ) )';

        if (!COwnerHelper::isCommunityAdmin()) {
            $pageModel = CFactory::getModel('pages');
            $pagesIds = $pageModel->getPageIds($my->id);

            $groupModel = CFactory::getModel('groups');
            $groupsIds = $groupModel->getGroupIds($my->id);

            $eventModel = CFactory::getModel('events');
            $eventsIds = $eventModel->getEventIds($my->id);

            if (count($pagesIds) > 0) {
                $extraSQL .= " AND (a.".$db->quoteName('pageid')." IN(" . implode(',', $pagesIds) . ") OR a.".$db->quoteName('pageid')." NOT IN (SELECT id FROM ".$db->quoteName('#__community_pages')." WHERE ".$db->quoteName('approvals')."='1' AND ".$db->quoteName('published')."='1'))";
            } else {
                $extraSQL .= " AND a.".$db->quoteName('pageid')." NOT IN(SELECT id FROM ".$db->quoteName('#__community_pages')." WHERE ".$db->quoteName('approvals')."='1' AND ".$db->quoteName('published')."='1') ";
            }

            if (count($groupsIds) > 0) {
                $extraSQL .= " AND (a.".$db->quoteName('groupid')." IN(" . implode(',', $groupsIds) . ") OR a.".$db->quoteName('groupid')." NOT IN (SELECT id FROM ".$db->quoteName('#__community_groups')." WHERE ".$db->quoteName('approvals')."='1' AND ".$db->quoteName('published')."='1'))";
            } else {
                $extraSQL .= " AND a.".$db->quoteName('groupid')." NOT IN(SELECT id FROM ".$db->quoteName('#__community_groups')." WHERE ".$db->quoteName('approvals')."='1' AND ".$db->quoteName('published')."='1') ";
            }

            if (count($eventsIds) > 0) {
                $extraSQL .= " AND (a.".$db->quoteName('eventid')." IN(" . implode(',', $eventsIds) . ") OR a.".$db->quoteName('eventid')." NOT IN (SELECT id FROM ".$db->quoteName('#__community_events')." WHERE ".$db->quoteName('published')."='1'))";
            } else {
                $extraSQL .= " AND a.".$db->quoteName('eventid')." NOT IN(SELECT id FROM ".$db->quoteName('#__community_events')." WHERE ".$db->quoteName('published')."='1') ";
            }

            if ($my->id > 0) {
                $blockLists = $my->getBlockedUsers();
                $blockedUserIds = array();
                foreach ($blockLists as $blocklist) {
                    $blockedUserIds[] = $blocklist->blocked_userid;
                }

                $blockedUserIds = implode(",", $blockedUserIds);

                if ($blockedUserIds) {
                    $extraSQL .= ' AND a.'.$db->quoteName('creator').' NOT IN (' . $blockedUserIds . ') ';
                }
            }

        }

		$query = 'SELECT '
                    .' a.*'
                    .' FROM '.$db->quoteName('#__community_polls').' as a '
                    .' WHERE a.'.$db->quoteName('published').'='.$db->Quote('1') .'  '
					. $extraSQL
					. $order;

		if(!$nolimit){
			$query .= ' LIMIT '.$limitstart .' , '.$limit;
		}

		$db->setQuery( $query );
		try {
			$rows = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_polls').' AS a '
				. 'WHERE a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );

		try {
			$this->total = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $this->total , $limitstart , $limit);
		}

		return $rows;
	}

	public function getPolls($userId = null, $sorting = null, $useLimit = true, $categoryId = null)
	{
		$db	= $this->getDBO();
		$my = CFactory::getUser();
		$extraSQL = '';
		
		if($userId > 0) {
            $extraSQL .= ' AND a.creator=' . $db->Quote($userId);
        }

		if( $categoryId )
		{
			$extraSQL	.= ' AND a.catid=' . $db->Quote($categoryId);
		}

		$order	= '';

		$limitSQL = '';
		$total		= 0;
		$limit		= $this->getState('limit');
		$limitstart = $this->getState('limitstart');
		if($useLimit){
			$limitSQL	= ' LIMIT ' . $limitstart . ',' . $limit ;
		}

		switch($sorting)
		{
			case 'alphabetical':
				if( empty($order) )
					$order	= 'ORDER BY a.'.$db->quoteName('title').' ASC ';
			default:
				if( empty($order) )
					$order	= ' ORDER BY a.created DESC ';

				$query = 'SELECT '
                    .' a.*'
                    .' FROM '.$db->quoteName('#__community_polls').' as a '
                    .' WHERE a.'.$db->quoteName('published').'='.$db->Quote('1') .'  '
					. $extraSQL
					. $order
					. $limitSQL;
				break;
		}
		
		$db->setQuery( $query );

		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_polls').' AS a '
				. 'WHERE a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );
		try {
			$total = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit );
		}

		return $result;
	}

	public function isCreator($userId, $pollId)
	{
		if ($userId == 0) return false;

		$db = $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_polls')
				. ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($pollId)
				. ' AND ' . $db->quoteName('creator') . '=' . $db->Quote($userId);
		$db->setQuery( $query );

		$isCreator = ($db->loadResult() >= 1) ? true : false;
		return $isCreator;
	}

	public function getPollItems($pollId)
	{
		$db	= $this->getDBO();

		$query	= 'SELECT * '
				. 'FROM ' . $db->quoteName('#__community_polls_items')
				. ' WHERE ' . $db->quoteName('poll_id') . '=' . $db->Quote($pollId)
				. ' ORDER BY count DESC, id ASC';
		$db->setQuery( $query );

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		CError::assert( $result , '', '!empty', __FILE__ , __LINE__ );
		return $result;
	}

	public function asPollItemVoter($pollId, $pollItemId, $userId)
	{
		$db	= $this->getDBO();

		$query	= 'SELECT * '
				. 'FROM ' . $db->quoteName('#__community_polls_users')
				. ' WHERE ' . $db->quoteName('poll_itemid') . '=' . $db->Quote($pollItemId)
				. ' AND ' . $db->quoteName('poll_id') . '=' . $db->Quote($pollId)
				. ' AND ' . $db->quoteName('user_id') . '=' . $db->Quote($userId);

		$db->setQuery($query);
		$db->execute();
        
        $count = $db->getNumRows();
        
		return $count;
	}

	/**
	 * Return the number of polls count for specific user
	 **/
	public function getPollsCount($userId)
	{
		if ($userId == 0) {
			return 0;
		}

		$db = $this->getDBO();
		$query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_polls')
			. ' WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($userId)
			. ' AND ' . $db->quoteName('published') . '=' . $db->Quote(1);

		$db->setQuery($query);
		$count	= $db->loadResult();

		return $count;
	}

	/**
	 * Return the number of polls count for specific event
	 **/
	public function getEventPollsCount($eventId)
	{ 
		if (!$eventId) {
			return 0;
		}

		$db = $this->getDBO();
		$query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_polls')
			. ' WHERE ' . $db->quoteName('eventid') . '=' . $db->Quote($eventId)
			. ' AND ' . $db->quoteName('published') . '=' . $db->Quote(1);

		$db->setQuery($query);
		$count	= $db->loadResult();

		return $count;
	}
}