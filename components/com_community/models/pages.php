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

class CommunityModelPages extends JCCModel
implements CLimitsInterface, CNotificationsInterface
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
    
    public function getCategories( $catId = COMMUNITY_ALL_CATEGORIES )
	{
		$db	=  $this->getDBO();

		$where	=   '';

		if( $catId !== COMMUNITY_ALL_CATEGORIES && ($catId != 0 || !is_null($catId )))
		{
			if( $catId === COMMUNITY_NO_PARENT )
			{
				$where	=   'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( COMMUNITY_NO_PARENT ) . ' ';
			}
			else
			{
				$where	=   'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( $catId ) . ' ';
			}
		}

		$query	=   'SELECT a.*, COUNT(b.'.$db->quoteName('id').') AS count '
			    . ' FROM ' . $db->quoteName('#__community_pages_category') . ' AS a '
			    . ' LEFT JOIN ' . $db->quoteName( '#__community_pages' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('categoryid')
			    . ' AND b.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
			    . $where
			    . ' GROUP BY a.'.$db->quoteName('id').' ORDER BY a.'.$db->quoteName('name').' ASC';

		$db->setQuery( $query );
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		return $result;
	}

	/**
	* Return all category.
	*
	* @access  public
	* @returns Array  An array of categories object
	* @since   Jomsocial 2.6
	**/
	public function getAllCategories()
	{
		$db     = $this->getDBO();

		$query  = 'SELECT *
					FROM ' . $db->quoteName('#__community_pages_category');

		$db->setQuery( $query );
		$result = $db->loadObjectList();

		// bind to table
		$data = array();
		foreach($result AS $row) {
			$groupCat = JTable::getInstance('PageCategory', 'CTable');
			$groupCat->bind($row);
			$data[] = $groupCat;
		}

		return $data;
	}

	/**
	 * Returns the category's group count
	 *
	 * @access  public
	 * @returns Array  An array of categories object
	 * @since   Jomsocial 2.4
	 **/
	function getCategoriesCount()
	{
		$db	=  $this->getDBO();

		$query = "SELECT c.id, c.parent, c.name, count(g.id) AS total, c.description
				  FROM " . $db->quoteName('#__community_pages_category') . " AS c
				  LEFT JOIN " . $db->quoteName('#__community_pages'). " AS g ON g.categoryid = c.id
							AND g." . $db->quoteName('published') . "=" . $db->Quote( '1' ) . "
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

	/**
	 * Returns the category name of the specific category
	 *
	 * @access public
	 * @param	string Category Id
	 * @returns string	Category name
	 **/
	public function getCategoryName($categoryId)
	{
		CError::assert($categoryId, '', '!empty', __FILE__ , __LINE__ );
		$db		= $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName('name') . ' '
				. 'FROM ' . $db->quoteName('#__community_pages_category') . ' '
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
    
    public function isMember($userid, $pageid)
    {
		// guest is not a member of any group
		if ($userid == 0) return false;

		$db	= $this->getDBO();
		$strSQL	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_pages_members')
				. ' WHERE ' . $db->quoteName('pageid') . '=' . $db->Quote($pageid)
				. ' AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
				. ' AND ' . $db->quoteName('approved') . '=' . $db->Quote( '1' )
				. ' AND ' . $db->quoteName('permissions') . '!=' . $db->Quote(COMMUNITY_PAGE_BANNED);


		$db->setQuery( $strSQL );
		$count	= $db->loadResult();
		return $count;
	}

	public function isAdmin($userid, $pageid)
	{
		if($userid == 0)
			return false;

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_pages_members') . ' '
				. ' WHERE ' . $db->quoteName('pageid') . '=' . $db->Quote($pageid) . ' '
				. ' AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
				. ' AND '.$db->quoteName('permissions').'=' . $db->Quote( '1' );

		$db->setQuery( $query );

        try {
            $isAdmin = ($db->loadResult() >= 1) ? true : false;
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		//@remove: in newer version we need to skip this test as we were using 'admin'
		// as the permission for the creator
		if( !$isAdmin )
		{
			$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_pages' ) . ' '
					. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $pageid ) . ' '
					. 'AND ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userid );
			$db->setQuery( $query );

            try {
                $isAdmin = ($db->loadResult() >= 1) ? true : false;
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

			// If user is admin, update necessary records
			if( $isAdmin )
			{
				$members = JTable::getInstance( 'PageMembers' , 'CTable' );
				$keys = array('memberId'=>$userid , 'pageId'=>$pageId );
				$members->load( $keys);
				$members->permissions	= '1';
				$members->store();
			}
		}

		return $isAdmin;
	}

	public function getPagesCreationCount( $userId )
	{
		// guest obviously has no group
		if($userId == 0)
			return 0;

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName( '#__community_pages' ) . ' '
				. 'WHERE ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userId );
		$db->setQuery( $query );

		$count	= $db->loadResult();

		return $count;
	}

	public function getTotalToday( $userId )
	{
		$date	= JDate::getInstance();
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_pages' ) . ' AS a '
				. ' WHERE a.'.$db->quoteName('ownerid').'=' . $db->Quote( $userId )
				. ' AND TO_DAYS(' . $db->Quote( $date->toSql( true ) ) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = '.$db->Quote(0);
		$db->setQuery( $query );

		$count		= $db->loadResult();

		return $count;
	}

	public function pageExist($name, $id=0) {
		$db		= $this->getDBO();

		$strSQL	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_pages')
			. ' WHERE '.$db->quoteName('name').'=' . $db->Quote( $name )
			. ' AND '.$db->quoteName('id').'!='. $db->Quote( $id ) ;


		$db->setQuery( $strSQL );
        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

		return $result;
	}
    
    public function getFeaturedPages(){
        $db	= $this->getDBO();

        $query	= 'SELECT cid FROM '. $db->quoteName('#__community_featured')
            . ' WHERE '. $db->quoteName('type').'=' . $db->Quote('pages');
        
        $db->setQuery($query);
        $results = $db->loadColumn();
        
        return $results;
    }

	public function getAllPages($categoryId = null , $sorting = null , $search = null , $limit = null , $skipDefaultAvatar = false , $hidePrivateGroup = false, $pagination = true, $nolimit = false)
	{
		$db = $this->getDBO();
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

        //special case for sorting by featured
        if($sorting == 'featured'){
            $featuredPages = $this->getFeaturedPages();

            if (count($featuredPages) > 0 ) {
                $featuredPages = implode(',', $featuredPages);
                $extraSQL .= ' AND a.id IN ('.$featuredPages.') ';
            } else {
				$extraSQL .= ' AND 1=0 ';
			}
        }

		// Test if search is parsed
		if (!is_null($search)) {
			$extraSQL .= ' AND a.'.$db->quoteName('name').' LIKE ' . $db->Quote( '%' . $search . '%' ) . ' ';
		}

		if ($skipDefaultAvatar) {
			$extraSQL .= ' AND ( a.'.$db->quoteName('thumb').' != ' . $db->Quote( DEFAULT_GROUP_THUMB ) . ' AND a.'.$db->quoteName('avatar').' != ' . $db->Quote( DEFAULT_GROUP_AVATAR ) . ' )';
		}

		$order	='';

		switch ($sorting) {
			case 'alphabetical':
				$order = ' ORDER BY a.'.$db->quoteName('name').' ASC ';
				break;
			case 'mostdiscussed':
				$order = ' ORDER BY '.$db->quoteName('discusscount').' DESC ';
				break;
			case 'mostwall':
				$order = ' ORDER BY '.$db->quoteName('wallcount').' DESC ';
				break;
			case 'mostmembers':
				$order = ' ORDER BY '.$db->quoteName('membercount').' DESC ';
				break;
            case 'hits' :
                $order = ' ORDER BY '.$db->quoteName('hits').' DESC ';
                break;
            case 'oldest':
				$order = ' ORDER BY a.'.$db->quoteName('created').' ASC ';
				break;
			default:
				$order = ' ORDER BY a.'.$db->quoteName('created').' DESC ';
				break;
		}

		if (!is_null($categoryId) && $categoryId != 0) {
            if (is_array($categoryId)) {
                if (count($categoryId) > 0) {
                    $categoryIds = implode(',', $categoryId);
                    $extraSQL .= ' AND a.' . $db->quoteName('categoryid'). ' IN(' . $categoryIds . ')';
                }
            } else {
                $extraSQL .= ' AND a.'.$db->quoteName('categoryid').'=' . $db->Quote($categoryId) . ' ';
            }
		}

        $user = CFactory::getUser();
        $userId = (int) $user->id;
        unset($user);

        if($userId > 0) {
            if(!COwnerHelper::isCommunityAdmin()) {
                $extraSQL.= '
	            AND ('
	                . 'a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('0')
	                .' OR ('
	                .'a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('1')
	                .' AND'
	                .' (SELECT COUNT(' . $db->quoteName('pageid').') FROM ' . $db->quoteName('#__community_pages_members') . ' as b WHERE b.'.$db->quoteName('memberid').'='. $db->quote($userId) .' and b.'.$db->quoteName('pageid').'=a.'.$db->quoteName('id').') > 0)
	            )';

                $my = CFactory::getUser();
                $blockLists = $my->getBlockedUsers();
                $blockedUserIds = array();
                foreach ($blockLists as $blocklist) {
                    $blockedUserIds[] = $blocklist->blocked_userid;
                }

                $blockedUserIds = implode(",", $blockedUserIds);

                if ($blockedUserIds) {
                    $extraSQL .= ' AND a.'.$db->quoteName('ownerid').' NOT IN (' . $blockedUserIds . ') ';
                }
            }
        } else {
            $extraSQL .= ' AND a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('0');
        }



		if ($sorting == 'mostactive') {
			$query	= 'SELECT *, (a.' . $db->quoteName('discusscount') . ' + a.' . $db->quoteName('wallcount') . ' ) AS count FROM ' . $db->quoteName('#__community_pages') . ' as a'
					. ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b ON a.' . $db->quoteName('id') . '= b.' . $db->quoteName('pageid')
					. ' WHERE a.' . $db->quoteName('published') . ' = ' . $db->Quote('1')
					. $extraSQL
					. ' GROUP BY a.' . $db->quoteName('id')
					. ' ORDER BY '.$db->quoteName('count').' DESC ';
		} else {
			$query = 'SELECT '
                .' a.*'
                .' FROM '.$db->quoteName('#__community_pages').' as a '
                .' WHERE a.'.$db->quoteName('published').'='.$db->Quote('1') .'  '
				. $extraSQL
				. $order;
		}

		if(!$nolimit){
			$query .= ' LIMIT '.$limitstart .' , '.$limit;
		}

		$db->setQuery( $query );
		try {
			$rows = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		if(!empty($rows)){
			//count members, some might be blocked, so we want to deduct from the total we currently have
			foreach($rows as $k => $r){
				$query = 'SELECT COUNT(*)
						  FROM ' . $db->quoteName('#__community_pages_members') . ' AS a
						  JOIN ' . $db->quoteName('#__users') . ' AS b ON a.memberid = b.id
						  WHERE `approved` = ' . $db->Quote('1') . ' AND b.block = 0 AND pageid = ' . $db->Quote($r->id);
 				
 				$db->setQuery($query);
 				$rows[$k]->membercount = $db->loadResult();

                // Get like
                $likes = new CLike();
                $rows[$k]->totalLikes = $likes->getLikeCount('pages', $r->id);

			}
		}

		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_pages').' AS a '
			. 'WHERE a.'.$db->quoteName('published').'=' . $db->Quote('1')
			. $extraSQL;

		$db->setQuery($query);

		try {
			$this->total = $db->loadResult();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination	= new JPagination($this->total, $limitstart, $limit);
		}

		return $rows;
	}

	public function getPages($userId = null, $sorting = null, $useLimit = true, $categoryId = null)
	{
		$db	= $this->getDBO();
		$my = CFactory::getUser();
		$extraSQL = '';
		
		if($userId > 0) {

            if(!COwnerHelper::isCommunityAdmin()) {
            $extraSQL.= '
            AND ('
                . 'a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('0')
                .' OR ('
                .'a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('1')
                .' AND'

                .' (SELECT COUNT(' . $db->quoteName('pageid').') FROM ' . $db->quoteName('#__community_pages_members') . ' as c WHERE c.'.$db->quoteName('memberid').'='. $db->quote($my->id) .' and c.'.$db->quoteName('pageid').' = a.'.$db->quoteName('id').') > 0)
                 )';
            }

            $extraSQL .= ' AND b.memberid=' . $db->Quote($userId);
        } else {
            $extraSQL .= ' AND a.' . $db->quoteName('unlisted') . ' = ' . $db->Quote('0');
        }

		if( $categoryId )
		{
			$extraSQL	.= ' AND a.categoryid=' . $db->Quote($categoryId);
		}

        //special case for sorting by featured
        if($sorting == 'featured'){
            $featuredPages = $this->getFeaturedPages();
            if(count($featuredPages) > 0 ){
                $featuredPages = implode(',', $featuredPages);
                $extraSQL .= ' AND a.id IN ('.$featuredPages.') ';
            }
        }

		$orderBy = '';
		$limitSQL = '';
		$total = 0;
		$limit = $this->getState('limit');
		$limitstart = $this->getState('limitstart');
		
		if ($useLimit) {
			$limitSQL = ' LIMIT ' . $limitstart . ',' . $limit ;
		}

		switch ($sorting) {
			case 'mostmembers':
				// Get the groups that this user is assigned to
				$query		= 'SELECT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_pages') . ' AS a '
							. ' LEFT JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
							. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('pageid')
							. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
							. $extraSQL;

				$db->setQuery( $query );
				
				try {
					$pagesid = $db->loadColumn();
				} catch (Exception $e) {
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}

				if ($pagesid) {
					$pagesid = implode( ',' , $pagesid );

					$query = 'SELECT a.* '
						. 'FROM ' . $db->quoteName('#__community_pages') . ' AS a '
						. ' WHERE a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
						. ' AND a.'.$db->quoteName('id').' IN (' . $pagesid . ') '
						. ' ORDER BY a.'.$db->quoteName('membercount').' DESC '
						. $limitSQL;
				}
				break;
			case 'mostwalls':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.'.$db->quoteName('wallcount').' DESC ';
			case 'alphabetical':
				if( empty($orderBy) )
					$orderBy	= 'ORDER BY a.'.$db->quoteName('name').' ASC ';
			case 'oldest':
				if( empty($orderBy) )
					$orderBy	= 'ORDER BY a.'.$db->quoteName('created').' ASC ';
			default:
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.created DESC ';

				$query	= 'SELECT a.* FROM '
						. $db->quoteName('#__community_pages') . ' AS a '
						. ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
						. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('pageid')
						. ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
						. ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
						. $extraSQL
						. $orderBy
						. $limitSQL;
				break;
		}
		
		$db->setQuery( $query );

		try {
			$result = $db->loadObjectList();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_pages') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
				. ' WHERE a.'.$db->quoteName('id').'=b.'.$db->quoteName('pageid')
				. ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
				. ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
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

    public function isInvited($userid, $pageid)
    {
        if($userid == 0)
        {
            return false;
        }

        $db =  $this->getDBO();

        $query  =   'SELECT * FROM ' . $db->quoteName('#__community_pages_invite') . ' '
                . 'WHERE ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $pageid ) . ' '
                . 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

        $db->setQuery( $query );

        $isInvited  = ( $db->loadResult() >= 1 ) ? true : false;

        return $isInvited;
    }
    
    public function getReviews($pageid, $limit = 0)
    {
        CError::assert($pageid , '', '!empty', __FILE__ , __LINE__);

        $db = $this->getDBO();
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        $limit = ($limit === 0) ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');

        $query  = 'SELECT a.* FROM '
                . $db->quoteName('#__community_ratings') . ' AS a '
                . ' WHERE a.'.$db->quoteName('type').'=' . $db->quote('pages')
                . ' AND a.'.$db->quoteName('cid').'=' . $db->quote($pageid);
        

        $query  .= ' ORDER BY userid = ' . $my->id . ' DESC, id DESC';

        if ($limit) {
            $query  .= ' LIMIT ' . $limitstart . ',' . $limit;
        }
        
        $db->setQuery( $query );

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        
        $query  = 'SELECT COUNT(a.id) FROM'
                . $db->quoteName('#__community_ratings') . ' AS a '
                . ' WHERE a.'.$db->quoteName('type').'=' . $db->quote('pages')
                . ' AND a.'.$db->quoteName('cid').'=' . $db->quote($pageid);

        $db->setQuery( $query );

        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        return $result;
    }

    public function getMembers( $pageid , $limit = 0 , $onlyApproved = true , $randomize = false , $loadAdmin = false, $ignoreLimit = false )
    {
        CError::assert( $pageid , '', '!empty', __FILE__ , __LINE__ );

        $db     = $this->getDBO();
                $config = CFactory::getConfig();
        $limit      = ($limit === 0) ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');

        $query  = 'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName($config->get( 'displayname')).' as name FROM'
                . $db->quoteName('#__community_pages_members') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                . ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
                . ' AND a.'.$db->quoteName('pageid').'=' . $db->Quote( $pageid )
                . ' AND b.'.$db->quoteName('block').'=' . $db->Quote( '0' ) . ' '
                . ' AND a.'.$db->quoteName('permissions').' !=' . $db->quote( COMMUNITY_GROUP_BANNED );

        if( $onlyApproved )
        {
            $query  .= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '1' );
        }
        else
        {
            $query  .= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '0' );
        }

        if( !$loadAdmin )
        {
            $query  .= ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( '0' );
        }

        $query  .= ' GROUP BY'.$db->quoteName('memberid');

        if( $randomize )
        {
            $query  .= ' ORDER BY RAND() ';
        }
        else
        {

            $query  .= ' ORDER BY b.`' . $config->get( 'displayname') . '`';
        }

        if( $limit && !$ignoreLimit )
        {
            $query  .= ' LIMIT ' . $limitstart . ',' . $limit;
        }
        
        $db->setQuery( $query );
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query = explode('FROM', $query);
        $query = explode('GROUP BY', $query[1]);
        $query = "SELECT COUNT(*) FROM".$query[0];

        $db->setQuery( $query );

        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if( empty($this->_pagination))
        {
            jimport('joomla.html.pagination');

            $this->_pagination  = new JPagination( $total , $limitstart , $limit);
        }

        return $result;
    }

    public function getBannedMembers( $pageid, $limit=0, $randomize=false )
    {
        CError::assert( $pageid , '', '!empty', __FILE__ , __LINE__ );

        $db     =   $this->getDBO();

        $limit      =   ($limit === 0) ? $this->getState('limit') : $limit;
        $limitstart =   $this->getState('limitstart');

        $query      =   'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName('name').' as name '
                . ' FROM '. $db->quoteName('#__community_pages_members') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                . ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
                . ' AND a.'.$db->quoteName('pageid').'=' . $db->Quote( $pageid )
                . ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( COMMUNITY_PAGE_BANNED );

        if( $randomize )
        {
            $query  .=  ' ORDER BY RAND() ';
        }

        if( !is_null($limit) )
        {
            $query  .=  ' LIMIT ' . $limitstart . ',' . $limit;
        }

        $db->setQuery( $query );

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query      =   'SELECT COUNT(*) FROM '
                . $db->quoteName('#__community_pages_members') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                . ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
                . ' AND a.'.$db->quoteName('pageid').'=' . $db->Quote( $pageid ) . ' '
                . ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( COMMUNITY_PAGE_BANNED );

        $db->setQuery( $query );
        try {
            $total = $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
        $this->total    =   $total;

        if( empty($this->_pagination) )
        {
            jimport( 'joomla.html.pagination' );
            $this->_pagination  =   new JPagination( $total, $limitstart, $limit );
        }

        return $result;
    }

    /**
     * See if the given user is waiting authorization for the page
     * @param   string  userid
     * @param   string  pageid
     */
    public function isWaitingAuthorization($userid, $pageid) {
        // guest is not a member of any page
        if($userid == 0)
            return false;

        $db = $this->getDBO();
        $strSQL = 'SELECT COUNT(*) FROM `#__community_pages_members` '
                . 'WHERE ' . $db->quoteName('pageid') . '=' . $db->Quote($pageid) . ' '
                . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
                . 'AND ' . $db->quoteName('approved') . '=' . $db->Quote(0);

        $db->setQuery( $strSQL );
        $count  = $db->loadResult();
        return $count;
    }

    static public function deletePageMembers($pid)
    {
        $db = JFactory::getDBO();
        $sql = "SELECT ".$db->quoteName('memberid')." FROM ".$db->quoteName("#__community_pages_members")." WHERE ".$db->quoteName("pageid")."=".$db->quote($pid);
        $db->setQuery($sql);
        $results = $db->loadColumn();

        foreach($results as $result){
            $user = CFactory::getUser($result);
            // user existing check
            if ($user->id > 0) {
                $pages = explode(',',$user->_pages);
                $filteredPage = array_diff( $pages, array($pid) );
                $pages = implode(',', $filteredPage);
                $user->_pages = $pages;
                $user->save();
            }
        }

        $sql = "DELETE

                FROM
                        ".$db->quoteName("#__community_pages_members")."
                WHERE
                        ".$db->quoteName("pageid")."=".$db->quote($pid);
        $db->setQuery($sql);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    static public function deletePageWall($pid)
    {
        $db = JFactory::getDBO();

        $sql = "DELETE

                FROM
                        ".$db->quoteName("#__community_wall")."
                WHERE
                        ".$db->quoteName("contentid")." = ".$db->quote($pid)." AND
                        ".$db->quoteName("type")." = ".$db->quote('pages');
        $db->setQuery($sql);
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        //Remove Page info from activity stream
        $sql = "Delete FROM " .$db->quoteName("#__community_activities"). "
                WHERE ". $db->quoteName("pageid") . " = ".$db->Quote($pid);

        $db->setQuery($sql);

        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    static public function deletePageMedia($pid)
    {
        $db             = JFactory::getDBO();
        $photosModel    = CFactory::getModel( 'photos' );
        $videoModel     = CFactory::getModel( 'videos' );
        $fileModel      = CFactory::getModel( 'files' );

        // page's photos removal.
        $albums = $photosModel->getPageAlbums($pid , false, false, 0);
        foreach ($albums as $item)
        {
            $photos = $photosModel->getAllPhotos($item->id, PHOTOS_PAGE_TYPE);

            foreach ($photos as $row)
            {
                $photo  = JTable::getInstance( 'Photo' , 'CTable' );
                $photo->load($row->id);
                $photo->delete();
            }

            //now we delete page photo album folder
            $album  = JTable::getInstance( 'Album' , 'CTable' );
            $album->load($item->id);
            $album->delete();
        }

        //page's videos


        $featuredVideo  = new CFeatured(FEATURED_VIDEOS);
        $videos         = $videoModel->getPageVideos($pid);

        foreach($videos as $vitem)
        {
            if (!$vitem) continue;

            $video      = JTable::getInstance( 'Video' , 'CTable' );
            $videaId    = (int) $vitem->id;

            $video->load($videaId);

            if($video->delete())
            {
                // Delete all videos related data
                $videoModel->deleteVideoWalls($videaId);
                $videoModel->deleteVideoActivities($videaId);

                //remove featured video
                $featuredVideo->delete($videaId);

                //remove the physical file
                $storage = CStorage::getStorage($video->storage);
                if ($storage->exists($video->thumb))
                {
                    $storage->delete($video->thumb);
                }

                if ($storage->exists($video->path))
                {
                    $storage->delete($video->path);
                }
            }

        }

        $fileModel->alldelete($pid,'page');

        return true;
    }

    public function getPageIds($userId)
    {
        $db     = $this->getDBO();
        $query      = 'SELECT DISTINCT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_pages') . ' AS a '
                . ' LEFT JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
                . ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('pageid')
                . ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
                . ' AND b.memberid=' . $db->Quote($userId)
                . ' AND a.published=1';

        $db->setQuery( $query );
        try {
            $pagesid = $db->loadColumn();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $pagesid;

    }

    public function getPageName($pageid)
    {
        $session = JFactory::getSession();
        $data = $session->get('pages_name_'.$pageid);
        if($data)
        {
            return $data;
        }
        $db = $this->getDBO();

        $query  =   'SELECT ' . $db->quoteName('name').' FROM ' . $db->quoteName('#__community_pages')
                    . " WHERE " . $db->quoteName("id") . "=" . $db->Quote($pageid);

        $db->setQuery( $query );

        $name = $db->loadResult();

        $session->set('pages_name_'.$pageid, $name);
        return $name;
    }

    public function isCreator($userId , $pageId)
    {
        // guest is not a member of any page
        if($userId == 0)
            return false;

        $db     = $this->getDBO();

        $query  = 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_pages' ) . ' '
                . 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $pageId ) . ' '
                . 'AND ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userId );
        $db->setQuery( $query );

        $isCreator  = ( $db->loadResult() >= 1 ) ? true : false;
        return $isCreator;
    }

    public function getInviteFriendsList($userid, $pageid){
        $db = $this->getDBO();

        $query  =   'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
                . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
                . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
                . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
                . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
                                    . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
                                    . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
                . ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_pages_members') . ' AS e  '
                                    . ' WHERE e.'.$db->quoteName('pageid').' = '.$db->Quote($pageid)
                                    . ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
                .')' ;

        $db->setQuery( $query );

        $friends = $db->loadColumn();

        return $friends;
    }

    public function removeMember( $data )
    {
        $db = $this->getDBO();

        $strSQL = 'DELETE FROM ' . $db->quoteName('#__community_pages_members') . ' '
                . 'WHERE ' . $db->quoteName('pageid') . '=' . $db->Quote( $data->pageid ) . ' '
                . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote( $data->memberid );

        $db->setQuery( $strSQL );
        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }

    public function getMembersId( $pageid , $onlyApproved = false )
    {
        $db     = $this->getDBO();

        $query  = 'SELECT a.'.$db->quoteName('memberid').' AS id FROM '
                . $db->quoteName('#__community_pages_members') . ' AS a '
                . 'JOIN ' . $db->quoteName('#__users') . ' AS b ON a.' . $db->quoteName('memberid') . '=b.' . $db->quoteName('id')
                . 'WHERE a.'.$db->quoteName('pageid').'=' . $db->Quote( $pageid );

        if( $onlyApproved ){
            $query  .= ' AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );
            $query  .= ' AND b.' . $db->quoteName('block') . '=0 ';
            $query  .= 'AND permissions!=' . $db->Quote(COMMUNITY_PAGE_BANNED);
        }

        $db->setQuery( $query );
        $result = $db->loadColumn();

        return $result;
    }

    public function getUserViewablepages($userid){
        $db = JFactory::getDbo();

        if(COwnerHelper::isCommunityAdmin($userid)){
            //if this is an admin, he should be able to see all the published groups, please do not change
            //this logic thinking admin should see unpublished groups too because this is used to show photo albums, etc
            $query = "SELECT id FROM ".$db->quoteName('#__community_pages')." WHERE "
                    .$db->quoteName('published')."=".$db->quote(1);

            $db->setQuery($query);
            $results = $db->loadColumn();
            return $results;
        }

        //this is normal user, so we will get all the listed events as well as the events that the user is in the part with
        $query = "SELECT id FROM ".$db->quoteName('#__community_pages')." WHERE "
                .$db->quoteName('approvals')."=".$db->quote(0)
                ." AND ".$db->quoteName('published')."=".$db->quote(1);

        $db->setQuery($query);
        $results = $db->loadColumn();

        $userJoinedPages = $this->getPageIds($userid);

        $results = array_unique(array_merge($results,$userJoinedPages));
        return $results;
    }

    public function getMembersCount( $id )
    {
        $db = $this->getDBO();

        if( !isset($this->membersCount[$id] ) )
        {
            $query  = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_pages_members') . ' '
                    . 'WHERE '.$db->quoteName('pageid').'=' . $db->Quote( $id ) . ' '
                    . 'AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );

            $db->setQuery( $query );
            try {
                $this->membersCount[$id] = $db->loadResult();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }
        return $this->membersCount[$id];
    }

    public function getInviteListByName($namePrefix, $userid, $cid, $limitstart = 0, $limit = 8){
        $db = $this->getDBO();
        
        $andName = '';
        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');
        if(!empty($namePrefix)){
            $andName    = ' AND b.' . $db->quoteName( $nameField ) . ' LIKE ' . $db->Quote( '%'.$namePrefix.'%' ) ;
        }
        $query  =   'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
                . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
                . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
                . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
                . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
                                    . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
                                    . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
                . ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_pages_members') . ' AS e  '
                                    . ' WHERE e.'.$db->quoteName('pageid').' = '.$db->Quote($cid)
                                    . ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
                .')'
                . $andName
                . ' ORDER BY b.' . $db->quoteName($nameField)
                . ' LIMIT ' . $limitstart.','.$limit
                ;
        $db->setQuery($query);
        $friends = $db->loadColumn();
        
        //calculate total
        $query  =   'SELECT COUNT(DISTINCT(a.'.$db->quoteName('connect_to').'))  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
                . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
                . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
                . ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
                . ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
                                    . ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
                                    . ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
                . ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
                                    . ' FROM '.$db->quoteName('#__community_pages_members') . ' AS e  '
                                    . ' WHERE e.'.$db->quoteName('pageid').' = '.$db->Quote($cid)
                                    . ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
                .')'
                . $andName;

        $db->setQuery($query);
        $this->total =  $db->loadResult();
        
        return $friends;
    }

    public function getPageInvites( $userId , $sorting = null )
    {
        $db         = $this->getDBO();
        $extraSQL   = ' AND a.userid=' . $db->Quote($userId);
        $orderBy    = '';
        $limit          = $this->getState('limit');
        $limitstart     = $this->getState('limitstart');
        $total          = 0;


        switch($sorting)
        {

            case 'mostmembers':
                // Get the pages that this user is assigned to
                $query      = 'SELECT a.'.$db->quoteName('pageid').' FROM ' . $db->quoteName('#__community_pages_invite') . ' AS a '
                            . ' LEFT JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
                            . ' ON a.'.$db->quoteName('pageid').'=b.'.$db->quoteName('pageid')
                            . ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
                            . $extraSQL;

                $db->setQuery( $query );
                try {
                    $pagesid = $db->loadColumn();
                } catch (Exception $e) {
                    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }

                if( $pagesid )
                {
                    $pagesid       = implode( ',' , $pagesid );

                    $query          = 'SELECT a.* '
                                    . ' FROM ' . $db->quoteName('#__community_pages_invite') . ' AS a '
                                    . ' INNER JOIN '.$db->quoteName('#__community_pages').' AS b '
                                    . ' ON a.'.$db->quoteName('pageid').'=b.'.$db->quoteName('id')
                                    . ' WHERE a.'.$db->quoteName('pageid').' IN (' . $pagesid . ') '
                                    . ' ORDER BY b.'.$db->quoteName('membercount').' DESC '
                                    . ' LIMIT ' . $limitstart . ',' . $limit;
                }
                break;
            case 'mostdiscussed':
                if( empty($orderBy) )
                    $orderBy    = ' ORDER BY b.'.$db->quoteName('discusscount').' DESC ';
            case 'mostwall':
                if( empty($orderBy) )
                    $orderBy    = ' ORDER BY b.'.$db->quoteName('wallcount').' DESC ';
            case 'alphabetical':
                if( empty($orderBy) )
                    $orderBy    = 'ORDER BY b.'.$db->quoteName('name').' ASC ';
            case 'oldest':
                if( empty($orderBy) )
                    $orderBy    = 'ORDER BY b.'.$db->quoteName('created').' ASC ';
            case 'mostactive':
                //@todo: Add sql queries for most active page

            default:
                if( empty($orderBy) )
                    $orderBy    = ' ORDER BY b.'.$db->quoteName('created').' DESC ';

                $query  = 'SELECT distinct a.* FROM '
                        . $db->quoteName('#__community_pages_invite') . ' AS a '
                        . ' INNER JOIN ' . $db->quoteName( '#__community_pages' ) . ' AS b ON a.'.$db->quoteName('pageid').'=b.'.$db->quoteName('id')
                        . ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS c ON a.'.$db->quoteName('pageid').'=c.'.$db->quoteName('pageid')
                        . ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
                        . ' AND b.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
                        . $extraSQL
                        . $orderBy
                        . 'LIMIT ' . $limitstart . ',' . $limit;
                break;
        }
        $db->setQuery( $query );
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $query  = 'SELECT COUNT(distinct b.'.$db->quoteName('id').') FROM ' . $db->quoteName('#__community_pages_invite') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName( '#__community_pages' ) . ' AS b '
                . ' ON a.'.$db->quoteName('pageid').'=b.'.$db->quoteName('id')
                . ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS c '
                . ' ON a.'.$db->quoteName('pageid').'=c.'.$db->quoteName('pageid')
                . ' WHERE b.'.$db->quoteName('published').'=' . $db->Quote( '1' )
                . ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
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

            $this->_pagination  = new JPagination( $total , $limitstart , $limit );
        }

        return $result;
    }
    
    public function & getPage($id)
    {
        $db     = $this->getDBO();

        $query  = 'SELECT a.*, b.'.$db->quoteName('name').' AS ownername , c.'.$db->quoteName('name').' AS category FROM '
                . $db->quoteName('#__community_pages') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                . ' INNER JOIN ' . $db->quoteName('#__community_pages_category') . ' AS c '
                . ' WHERE a.'.$db->quoteName('id').'=' . $db->Quote( $id ) . ' '
                . ' AND a.'.$db->quoteName('ownerid').'=b.'.$db->quoteName('id')
                . ' AND a.'.$db->quoteName('categoryid').'=c.'.$db->quoteName('id');

        $db->setQuery( $query );
        try {
            $result = $db->loadObject();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }

    public function getTotalNotifications( $user )
    {
        if($user->_cparams->get('notif_pages_invite', true))
        {
            $privatePageRequestCount=0;

            if($user->_cparams->get('notif_pages_member_join'))
            {
                $allPages    =   $this->getAdminPages( $user->id , COMMUNITY_PRIVATE_PAGE);

                foreach($allPages as $pages)
                {
                    $member     =    $this->getMembers( $pages->id , null, false );

                    if(!empty($member))
                    {
                       $privatePageRequestCount += count($member);
                    }
                }
            }

            return (int) $this->countPending( $user->id ) + $privatePageRequestCount;
        }

        return 0;
    }
    
    public function countPending($userId)
    {

        $db = $this->getDBO();

        $query  = 'SELECT COUNT(*) FROM '
        . $db->quoteName('#__community_pages_invite') . ' AS a '
        . ' INNER JOIN ' . $db->quoteName( '#__community_pages' ) . ' AS b ON a.'.$db->quoteName('pageid').'=b.'.$db->quoteName('id')
                    . ' AND a.' .$db->quoteName('userid'). '=' . $db->Quote($userId);

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return 0;
        }
    }

    public function getAdminPages( $userId, $privacy = NULL )
    {
        $extraSQL = NULL;
        $db     = $this->getDBO();

        if( $privacy == COMMUNITY_PRIVATE_PAGE )
        {
            $extraSQL = ' AND a.'.$db->quoteName('approvals').'=' . $db->Quote( '1' );
        }

        if( $privacy == COMMUNITY_PUBLIC_PAGE )
        {
            $extraSQL = ' AND a.'.$db->quoteName('approvals').'=' . $db->Quote( '0' );
        }
        $query  =   'SELECT a.* FROM '
                        . $db->quoteName('#__community_pages') . ' AS a '
                        . ' INNER JOIN ' . $db->quoteName('#__community_pages_members') . ' AS b '
                        . ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('pageid')
                        . ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
                        . ' AND b.'.$db->quoteName('permissions').'=' . $db->Quote( '1' )
                        . ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
                        . ' AND b.'.$db->quoteName('memberid').'=' . $db->Quote($userId)
                        . $extraSQL;

        $db->setQuery( $query );
        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // bind to table
        $data = array();
        foreach($result as $row){
            $pageAdmin = JTable::getInstance( 'Page' , 'CTable' );
            $pageAdmin->bind( $row );
            $data[] = $pageAdmin;
        }

        return $data;
    }

    public function getInvitors($userid, $pageid)
    {
        if($userid == 0)
        {
            return false;
        }

        $db =  $this->getDBO();

        $query  =   'SELECT DISTINCT(' . $db->quoteName( 'creator' ) . ') FROM ' . $db->quoteName('#__community_pages_invite') . ' '
                . 'WHERE ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $pageid ) . ' '
                . 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

        $db->setQuery( $query );

        $results  = $db->loadObjectList();

        // bind to table
        $data = array();
        foreach($results AS $row) {
            $invitor = JTable::getInstance('PageInvite', 'CTable');
            $invitor->bind($row);
            $data[] = $invitor;
        }

        return $data;
    }

    public function getFriendsCount($userid, $pageid)
    {
        $db = $this->getDBO();

        $query  =   'SELECT COUNT(DISTINCT(a.'.$db->quoteName('connect_to').')) AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
                . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
                . ' INNER JOIN ' . $db->quoteName( '#__community_pages_members' ) . ' AS c '
                . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
                . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
                . ' AND c.'.$db->quoteName('pageid').'=' . $db->Quote( $pageid )
                . ' AND a.'.$db->quoteName('connect_to').'=c.'.$db->quoteName('memberid')
                . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
                . ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' );

        $db->setQuery( $query );

        $total = $db->loadResult();

        return $total;
    }

    public function updatePage($data)
    {
        $db = $this->getDBO();

        if ($data->id == 0) {
            // New record, insert it.
            try {
                $db->insertObject('#__community_pages', $data);
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            $data->id = $db->insertid();

            // Insert an object for this user in the #__community_pages_members as well
            $members = new stdClass();
            $members->pageid = $data->id;
            $members->memberid = $data->ownerid;

            // Creator should always be 1 as approved as they are the creator.
            $members->approved = 1;
            $members->permissions = 'admin';

            try {
                $db->insertObject('#__community_pages_members', $members);
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        } else {
            // Old record, update it.
            $db->updateObject( '#__community_pages' , $data , 'id');
        }
        
        return $data->id;
    }

    /**
     * Gets the pages property if it requires an approval or not.
     *
     * param    string  id The id of the page.
     *
     * return   boolean True if it requires approval and False otherwise
     **/
    public function needsApproval( $id )
    {
        $db     = $this->getDBO();
        $query  = 'SELECT ' . $db->quoteName( 'approvals' ) . ' FROM '
                . $db->quoteName( '#__community_pages' ) . ' WHERE '
                . $db->quoteName( 'id' ) . '=' . $db->Quote( $id );

        $db->setQuery( $query );
        $result = $db->loadResult();

        return ( $result == '1' );
    }
}