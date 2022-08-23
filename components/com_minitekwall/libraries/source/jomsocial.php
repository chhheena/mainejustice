<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$js = JPATH_ROOT.DS.'components'.DS.'com_community';
if (file_exists($js.DS.'community.php'))
{
	include_once(JPATH_BASE.'/components/com_community/defines.community.php');
	require_once(JPATH_BASE .'/components/com_community/libraries/core.php');
	require_once(JPATH_BASE .'/components/com_community/libraries/messaging.php');
}

class MinitekWallLibSourceJomsocial
{
	// Get user's friends
	public function getFriendIds($id)
  	{
    	if ($id == 0)
		{
			$fid = array();
			return $fid;
		}

    	$db		= JFactory::getDBO();
	  	$query	= 'SELECT DISTINCT(a.'. $db->quoteName('connect_to').') AS '.$db->quoteName('id')
					.' FROM '.$db->quoteName('#__community_connection').' AS a '
					.' INNER JOIN '.$db->quoteName( '#__users' ).' AS b '
					.' ON a.'.$db->quoteName('connect_from').' = '.$db->Quote( $id ).' '
					.' AND a.'.$db->quoteName('connect_to').' = b.'.$db->quoteName('id')
					.' AND a.'.$db->quoteName('status').' = '.$db->Quote('1');
        $db->setQuery( $query );

		$friends = $db->loadColumn();

		return $friends;
  	}

	// Get groups where user is member
	public function getGroupIds($userID)
	{
		$db = JFactory::getDBO();
		$query		= 'SELECT DISTINCT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_groups') . ' AS a '
				. ' LEFT JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
				. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
				. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
				. ' AND b.memberid=' . $db->Quote($userID);

		$db->setQuery( $query );
		$groupsid		= $db->loadColumn();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $groupsid;
	}

	// Get events that user is attending
	public function getEventIds($userID)
	{
		$db = JFactory::getDBO();
		$query		= 'SELECT DISTINCT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_events') . ' AS a '
				. ' LEFT JOIN ' . $db->quoteName('#__community_events_members') . ' AS b '
				. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('eventid')
				. ' WHERE b.'.$db->quoteName('status').'=' . $db->Quote( '1' )
				. ' AND b.'.$db->quoteName('memberid'). '=' . $db->Quote($userID);

		$db->setQuery( $query );
		$eventsid = $db->loadColumn();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $eventsid;
	}

	// Get video category name
	public function getVideoCategory( $groupID )
    {
    	$db		= JFactory::getDBO();
		$query	= 'SELECT ' . $db->quoteName( 'name' ) . ' FROM '
				. $db->quoteName( '#__community_videos_category' ) . ' '
				. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $groupID );

		$db->setQuery( $query );
		$category = $db->loadObject();
		$result = $category->name;

		return $result;
    }

	// Truncate text
	public function wordLimit($str, $limit = 100, $end_char = '&#8230;')
	{
		if (JString::trim($str) == '')
			return $str;

		// always strip tags for text
		$str = strip_tags($str);

		$find = array("/\r|\n/u", "/\t/u", "/\s\s+/u");
		$replace = array(" ", " ", " ");
		$str = preg_replace($find, $replace, $str);

		preg_match('/\s*(?:\S*\s*){'.(int)$limit.'}/u', $str, $matches);
		if (JString::strlen($matches[0]) == JString::strlen($str))
			$end_char = '';
		return JString::rtrim($matches[0]).$end_char;
	}

	// Get Jomsocial Users
	public function getJomsocialUsers($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$language = JFactory::getLanguage();
		$language->load('com_community.country', JPATH_SITE, $language->getTag(), true);

		$users_permissions = $data_source['jsu_respect_permissions'];

		// Specific users
		$specific_users = $data_source['jsu_specific_users'];
		if (preg_match('/^[0-9,]+$/i', $specific_users)) {
			$specific_users = trim($specific_users, ',');
			$specific_users = explode(',', $specific_users);
			JArrayHelper::toInteger($specific_users);
			$specific_str = implode(',', $specific_users);
		} else {
			$specific_str = 0;
		}

		// Excluded users
		$exclude_users = $data_source['jsu_exclude_users'];
		if (preg_match('/^[0-9,]+$/i', $exclude_users)) {
			$exclude_users = trim($exclude_users, ',');
			$exclude_users = explode(',', $exclude_users);
			JArrayHelper::toInteger($exclude_users);
			$excluded_str = implode(',', $exclude_users);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Enabled users
		$where .= ' WHERE b.'.$db->quoteName('block').' = '.$db->quote('0');

		// Users with avatar
		$users_without_photo = $data_source['jsu_exclude_users_no_photo'];
		if (!$users_without_photo) {
			$where .= ' AND a.'.$db->quoteName('avatar').' <> "" ';
		}

		// Featured users
		$featured_users = $data_source['jsu_featured_users'];
		if ($featured_users == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_users) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Guest
		if ($user->guest)
		{

			if ($users_permissions) // Only public profiles
			{
				$where .= ' AND a.'.$db->quoteName('params').' LIKE '.$db->Quote('%"privacyProfileView":0%');
			}

		}

		// Member
		if ($my)
		{

		  	// User is member - Permissions are on
		  	if ($users_permissions)
			{
			  	// Get user Friends
				$my_friends = $this->getFriendIds($my);
				$my_friends = implode(',', $my_friends);

				$where	.= ' AND (a.'.$db->quoteName('params').' LIKE '.$db->Quote('%"privacyProfileView":0%');
				$where	.= ' OR a.'.$db->quoteName('params').' LIKE '.$db->Quote('%"privacyProfileView":"20%');
				if ($my_friends)
				{
					$where	.= ' OR (a.'.$db->quoteName('params').' LIKE '.$db->Quote('%"privacyProfileView":"30%');
					$where	.=  ' AND a.'.$db->quoteName('userid').' IN ('.$my_friends.'))';
				}
				// Show my self
				$where	.=  ' OR a.'.$db->quoteName('userid').' = '.$db->Quote($my).' ';

				$where	.=  ')';

			}

		}

		// Specific users
		if ($specific_str)
		{
			$where .= ' AND a.'.$db->quoteName('userid').' IN ('.$specific_str.') ';
		}

		// Exclude users
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('userid').' NOT IN ('.$excluded_str.') ';
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query = 'SELECT a.* FROM ' . $db->quoteName( '#__community_users') . ' AS a';
		$query	.= ' LEFT JOIN ' . $db->quoteName( '#__users') . ' AS b';
		$query	.= ' ON a.'.$db->quoteName('userid').' = b.'.$db->quoteName('id').' ';

		// Featured users
		if (!$featured_users || $featured_users == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('userid').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'users' );
		}


		$query	.= $where;

		// Ordering
		$ordering = $data_source['jsu_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jsu_ordering_direction'];
		switch ($ordering)
		{
			case 'userid':
				$query .= ' a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'name':
				$query .= ' b.'.$db->quoteName('name').' '.$ordering_dir.', a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'registerDate':
				$query .= ' b.'.$db->quoteName('registerDate').' '.$ordering_dir.', a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'points':
				$query .= ' a.'.$db->quoteName('points').' '.$ordering_dir.', a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'friendcount':
				$query .= ' a.'.$db->quoteName('friendcount').' '.$ordering_dir.', a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'view':
				$query .= ' a.'.$db->quoteName('view').' '.$ordering_dir.', a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('userid').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;
		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Jomsocial Groups
	public function getJomsocialGroups($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

	  $groups_hide_private = $data_source['jsg_hide_private'];

		// Specific groups
		$specific_groups = $data_source['jsg_specific_groups'];
		if (preg_match('/^[0-9,]+$/i', $specific_groups)) {
			$specific_groups = trim($specific_groups, ',');
			$specific_groups = explode(',', $specific_groups);
			JArrayHelper::toInteger($specific_groups);
			$specific_str = implode(',', $specific_groups);
		} else {
			$specific_str = 0;
		}

		// Excluded groups
		$exclude_groups = $data_source['jsg_exclude_groups'];
		if (preg_match('/^[0-9,]+$/i', $exclude_groups)) {
			$exclude_groups = trim($exclude_groups, ',');
			$exclude_groups = explode(',', $exclude_groups);
			JArrayHelper::toInteger($exclude_groups);
			$excluded_str = implode(',', $exclude_groups);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Published groups
		$where	.= ' WHERE a.'.$db->quoteName('published').' = '.$db->quote('1');

		// Groups with avatar
		$groups_without_photo = $data_source['jsg_exclude_groups_no_photo'];
		if (!$groups_without_photo) {
			$where .= ' AND a.'.$db->quoteName('avatar').' <> "" ';
		}

		// Featured groups
		$featured_groups = $data_source['jsg_featured_groups'];
		if ($featured_groups == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_groups) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Guest
		if ($user->guest)
		{

			if ($groups_hide_private) // Only public groups
			{
				$where .= ' AND a.'.$db->quoteName('approvals').' = '.$db->Quote('0');
			}

		}

		// Member
		if ($my)
		{

		  	if ($groups_hide_private) // Only public groups	+ my groups
			{
			  	$where .= ' AND (';
				$where .= ' a.'.$db->quoteName('approvals').' = '.$db->Quote('0');

				$user_groups = $this->getGroupIds($my);
  				$user_groups_sql = @implode(',', $user_groups);

				if ($user_groups_sql) {
					$where .= ' OR';
					$where .= ' (a.'.$db->quoteName('approvals').' = '.$db->Quote('1').' AND a.'.$db->quoteName('id').' IN ('.$user_groups_sql.'))';
				}
				$where .= ')';
			}

		}

		// Specific categories
		if (array_key_exists('jsg_groupscategories', $data_source))
		{
			$specific_categories = $data_source['jsg_groupscategories'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$where .= ' AND a.'.$db->quoteName('categoryid').' IN ('.$specific_cat.') ';
			}
		}

		// Specific groups
		if ($specific_str && !$specific_categories)
		{
			$where .= ' AND a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude groups
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.')';
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query = 'SELECT a.* FROM ' . $db->quoteName( '#__community_groups') . ' AS a';
		$query .= ' INNER JOIN ' . $db->quoteName( '#__community_groups_category') . ' AS b';
		$query .= ' ON a.'.$db->quoteName('categoryid').' = b.'.$db->quoteName('id').' ';

		// Featured groups
		if (!$featured_groups || $featured_groups == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('id').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'groups' );
		}

		$query .= $where;

		// Ordering
		$ordering = $data_source['jsg_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jsg_ordering_direction'];
		switch ($ordering)
		{
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'name':
				$query .= ' a.'.$db->quoteName('name').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'membercount':
				$query .= ' a.'.$db->quoteName('membercount').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;

		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Jomsocial Events
	public function getJomsocialEvents($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$events_hide_private = $data_source['jse_events_hide_private'];

		// Specific events
		$specific_events = $data_source['jse_specific_events'];
		if (preg_match('/^[0-9,]+$/i', $specific_events)) {
			$specific_events = trim($specific_events, ',');
			$specific_events = explode(',', $specific_events);
			JArrayHelper::toInteger($specific_events);
			$specific_str = implode(',', $specific_events);
		} else {
			$specific_str = 0;
		}

		// Excluded events
		$exclude_events = $data_source['jse_exclude_events'];
		if (preg_match('/^[0-9,]+$/i', $exclude_events)) {
			$exclude_events = trim($exclude_events, ',');
			$exclude_events = explode(',', $exclude_events);
			JArrayHelper::toInteger($exclude_events);
			$excluded_str = implode(',', $exclude_events);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Published events
		$where	.= ' WHERE a.'.$db->quoteName('published').' = '.$db->quote('1');

		// Events with avatar
		$events_without_photo = $data_source['jse_exclude_events_no_photo'];
		if (!$events_without_photo) {
			$where .= ' AND a.'.$db->quoteName('avatar').' IS NOT NULL ';
		}

		// Featured events
		$featured_events = $data_source['jse_featured_events'];
		if ($featured_events == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_events) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Guest
		if ($user->guest)
		{

		  	if ($events_hide_private) // Only public events
			{
			    $where	.= ' AND (';
				$where	.= ' ( a.'.$db->quoteName('permission').' = '.$db->Quote('0').' AND a.'.$db->quoteName('contentid').' = '.$db->Quote('0').' )';
				$where	.= ' OR ';
				$where	.= ' ( a.'.$db->quoteName('permission').' = '.$db->Quote('0').' AND a.'.$db->quoteName('contentid').' > '.$db->Quote('0').' AND c.'.$db->quoteName('approvals').' = '. $db->Quote('0').')';
				$where	.= ' ) ';
			}
		}

		// Member
		if ($user->id)
		{

		  	if ($events_hide_private) // Only public events + my events
			{

				// Get my groups
  				$my_groups = $this->getGroupIds($my);
  				$my_groups_sql = @implode(',', $my_groups);

				// Get my events
				$my_events = $this->getEventIds($my);
  				$my_events_sql = @implode(',', $my_events);

				$where	.= ' AND (';
				$where	.= ' ( a.'.$db->quoteName('permission').' = '.$db->Quote('0').' AND a.'.$db->quoteName('contentid').' = '.$db->Quote('0').')';  // public event
				if ($my_groups_sql) {
					$where	.= ' OR ';
					$where	.= ' ( a.'.$db->quoteName('permission').' = '.$db->Quote('1').' AND a.'.$db->quoteName('id').' IN ('.$my_events_sql.'))'; // private event
				}
				$where	.= ' OR ';
				$where	.= ' ( a.'.$db->quoteName('permission').' = '.$db->Quote('0').' AND a.'.$db->quoteName('contentid').' > '.$db->Quote('0').' AND c.'.$db->quoteName('approvals').' = '.$db->Quote('0').')'; // public group event
				if ($my_groups_sql) {
					$where	.= ' OR ';
					$where	.= ' ( a.'.$db->quoteName('contentid').' > '.$db->Quote('0').' AND c.'.$db->quoteName('approvals').' = '.$db->Quote('1').' AND c.'.$db->quoteName('id').' IN ('.$my_groups_sql.'))'; // private group event
				}
				$where	.= ' ) ';

			}

		}

		// Specific categories
		if (array_key_exists('jse_eventscategories',$data_source ))
		{
			$specific_categories = $data_source['jse_eventscategories'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$where .= ' AND a.'.$db->quoteName('catid').' IN ('.$specific_cat.') ';
			}
		}

		// Specific events
		if ($specific_str && !$specific_categories)
		{
			$where .= ' AND a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude events
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.')';
		}

		// Remove old events
		$where .= ' AND a.'.$db->quoteName('enddate').' > DATE(NOW())';

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query	= 'SELECT a.* FROM '.$db->quoteName( '#__community_events').' AS a';
		$query	.= ' INNER JOIN '.$db->quoteName( '#__community_events_category').' AS b';
		$query	.= ' ON a.'.$db->quoteName('catid').' = b.'.$db->quoteName('id').' ';
		$query	.= ' LEFT JOIN '. $db->quoteName( '#__community_groups').' AS c';
		$query	.= ' ON a.'.$db->quoteName('contentid').' = c.'.$db->quoteName('id').' ';

		// Featured events
		if (!$featured_events || $featured_events == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('id').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'events' );
		}

		$query	.= $where;

		// Ordering
		$ordering = $data_source['jse_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jse_ordering_direction'];
		switch ($ordering)
		{
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'title':
				$query .= ' a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'startdate':
				$query .= ' a.'.$db->quoteName('startdate').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'enddate':
				$query .= ' a.'.$db->quoteName('enddate').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'confirmedcount':
				$query .= ' a.'.$db->quoteName('confirmedcount').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'ticket':
				$query .= ' a.'.$db->quoteName('ticket').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;

		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Jomsocial Photos
	public function getJomsocialPhotos($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$photos_permissions = $data_source['jsp_respect_permissions'];

		// Specific photos
		$specific_photos = $data_source['jsp_specific_photos'];
		if (preg_match('/^[0-9,]+$/i', $specific_photos)) {
			$specific_photos = trim($specific_photos, ',');
			$specific_photos = explode(',', $specific_photos);
			JArrayHelper::toInteger($specific_photos);
			$specific_str = implode(',', $specific_photos);
		} else {
			$specific_str = 0;
		}

		// Excluded photos
		$exclude_photos = $data_source['jsp_exclude_photos'];
		if (preg_match('/^[0-9,]+$/i', $exclude_photos)) {
			$exclude_photos = trim($exclude_photos, ',');
			$exclude_photos = explode(',', $exclude_photos);
			JArrayHelper::toInteger($exclude_photos);
			$excluded_str = implode(',', $exclude_photos);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Published
		$where	.= ' WHERE a.'.$db->quoteName('published').' = '.$db->Quote('1');

		// Featured albums

		$featured_photos = $data_source['jsp_featured_photos'];
		if ($featured_photos == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_photos) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Include covers
		$include_covers = $data_source['jsp_exclude_covers'];
		if (!$include_covers) { // hide covers
			$where .= ' AND b.'.$db->quoteName('type').' IN ("user","group") ';
		}

		// Guest
		if ($user->guest)
		{
		  	// 0 / 10 = Public, 20 = Members, 30 = Friends, 40 = Only me
		  	if ($photos_permissions)
			{
			  	$where	.= ' AND a.'.$db->quoteName('permissions').' IN (0,10) ';
			}

		}

		// Member
		if ($user->id)
		{

			// User is member - Permissions are on
			if ($photos_permissions) // Only public albums + members albums + friends albums + my groups albums + my albums
			{

				$where	.= ' AND (';

				// Get public photos and members photos
  				$where	.= ' a.'.$db->quoteName('permissions').' IN (0,10,20) ';

				// Get my photos
				$where	.= ' OR (a.'.$db->quoteName('creator').' = '.$db->quote($my).' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').')';

				// Get my friends photos
				$my_friends = $this->getFriendIds($my);
				$my_friends_sql = @implode(',', $my_friends);
				if ($my_friends_sql)
				{
					$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('30').' AND a.'.$db->quoteName('creator').' IN ('.$my_friends_sql.'))';
				}

				// Get my groups photos
				$my_groups = $this->getGroupIds($my);
				$my_groups_sql = @implode(',', $my_groups);
				if ($my_groups_sql)
				{
					$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('35').' AND b.'.$db->quoteName('groupid').' IN ('.$my_groups_sql.'))';
				}

				$where	.= ' ) ';

			// User is member - Permissions are off
			} else {

				$where	.= ' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').' ';

			}

		}

		// Specific albums
		if (array_key_exists('jsp_photosalbums', $data_source))
		{
			$specific_categories = $data_source['jsp_photosalbums'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$where .= ' AND a.'.$db->quoteName('albumid').' IN ('.$specific_cat.') ';
			}
		}

		// Specific photos
		if ($specific_str && !$specific_categories)
		{
			$where .= ' AND a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude photos
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.')';
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query	= 'SELECT a.* FROM '.$db->quoteName( '#__community_photos').' AS a';
		$query	.= ' INNER JOIN '. $db->quoteName( '#__community_photos_albums').' AS b';
		$query	.= ' ON a.'.$db->quoteName('albumid'). ' = b.'.$db->quoteName('id').' ';

		// Featured albums
		if (!$featured_photos || $featured_photos == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('albumid').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'albums' );
		}

		$query	.= $where;

		// Ordering
		$ordering = $data_source['jsp_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jsp_ordering_direction'];
		switch ($ordering)
		{
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'caption':
				$query .= ' a.'.$db->quoteName('caption').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result) {
				$result = array();
			}

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;

		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Jomsocial Albums
	public function getJomsocialAlbums($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$albums_permissions = $data_source['jsa_respect_permissions'];

		// Specific albums
		$specific_str = 0;
		if (array_key_exists('jsa_specific_albums', $data_source))
		{
			$specific_albums = $data_source['jsa_specific_albums'];
			if ($specific_albums) {
				JArrayHelper::toInteger($specific_albums);
				$specific_str = implode(',', $specific_albums);
			} else {
				$specific_str = 0;
			}
		}

		// Specific users albums
		$specific_users_albums = $data_source['jsa_specific_users'];
		if (preg_match('/^[0-9,]+$/i', $specific_users_albums)) {
			$specific_users_albums = trim($specific_users_albums, ',');
			$specific_users_albums = explode(',', $specific_users_albums);
			JArrayHelper::toInteger($specific_users_albums);
			$specific_users_str = implode(',', $specific_users_albums);
		} else {
			$specific_users_str = 0;
		}

		// Excluded albums
		$exclude_albums = $data_source['jsa_exclude_albums'];
		if (preg_match('/^[0-9,]+$/i', $exclude_albums)) {
			$exclude_albums = trim($exclude_albums, ',');
			$exclude_albums = explode(',', $exclude_albums);
			JArrayHelper::toInteger($exclude_albums);
			$excluded_str = implode(',', $exclude_albums);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Published
		$where	.= ' WHERE a.'.$db->quoteName('id').' > '.$db->Quote('0');

		// Featured albums
		$featured_albums = $data_source['jsa_featured_albums'];
		if ($featured_albums == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_albums) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Include covers
		$include_covers = $data_source['jsa_exclude_covers'];
		if (!$include_covers) { // hide covers
			$where .= ' AND a.'.$db->quoteName('type').' IN ("user","group") ';
		}

		// Guest
		if ($user->guest)
		{

		  	if ($albums_permissions)
			{
			  	$where	.= ' AND a.'.$db->quoteName('permissions').' IN (0,10)';
			}

		}

		// Member
		if ($user->id)
		{

			// User is member - Permissions are on
		  	if ($albums_permissions) // Only public albums + friends albums + my groups albums + my albums
			{

			  	$where	.= ' AND (';

				// Get public albums
				$where	.= ' a.'.$db->quoteName('permissions').' IN (0,10,20)';

				// Get my albums
				$where	.= ' OR (a.'.$db->quoteName('creator').' = '.$db->quote($my).' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').')';

				// Get my friends albums
				$my_friends = $this->getFriendIds($my);
				$my_friends_sql = @implode(',', $my_friends);

				if ($my_friends_sql)
				{
					$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('30').' AND a.'.$db->quoteName('creator').' IN ('.$my_friends_sql.'))';
				}

				// Get my groups albums
				$my_groups = $this->getGroupIds($my);
				$my_groups_sql = @implode(',', $my_groups);
				if ($my_groups_sql)
				{
					$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('35').' AND a.'.$db->quoteName('groupid').' IN ('.$my_groups_sql.'))';
				}

				$where .= ' ) ';

			// User is member - Permissions are off
			} else {

			  	$where	.= ' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').' ';

			}

		}

		// Specific albums
		if ($specific_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Specific users albums
		if (!$specific_str && $specific_users_str)
		{
			$where .= ' AND a.'.$db->quoteName('creator').' IN ('.$specific_users_str.') ';
		}

		// Exclude albums
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.') ';
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query	= 'SELECT a.* FROM '.$db->quoteName( '#__community_photos_albums').' AS a';
		$query	.= ' INNER JOIN '.$db->quoteName( '#__community_photos').' AS b';
		$query	.= ' ON a.'.$db->quoteName('photoid').' = b.'.$db->quoteName('id').' ';

		// Featured albums
		if (!$featured_albums || $featured_albums == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('id').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'albums' );
		}

		$query	.= $where;

		// Ordering
		$ordering = $data_source['jsa_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jsa_ordering_direction'];
		switch ($ordering)
		{
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'name':
				$query .= ' a.'.$db->quoteName('name').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

 		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;

		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Jomsocial Videos
	public function getJomsocialVideos($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$videos_permissions = $data_source['jsv_respect_permissions'];

		// Specific videos
		$specific_videos = $data_source['jsv_specific_videos'];
		if (preg_match('/^[0-9,]+$/i', $specific_videos)) {
			$specific_videos = trim($specific_videos, ',');
			$specific_videos = explode(',', $specific_videos);
			JArrayHelper::toInteger($specific_videos);
			$specific_str = implode(',', $specific_videos);
		} else {
			$specific_str = 0;
		}

		// Excluded videos
		$exclude_videos = $data_source['jsv_exclude_videos'];
		if (preg_match('/^[0-9,]+$/i', $exclude_videos)) {
			$exclude_videos = trim($exclude_videos, ',');
			$exclude_videos = explode(',', $exclude_videos);
			JArrayHelper::toInteger($exclude_videos);
			$excluded_str = implode(',', $exclude_videos);
		} else {
			 $excluded_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();

		$where = '';

		// Published
		$where	.= ' WHERE a.'.$db->quoteName('published').' = '. $db->quote('1');

		// Featured videos
		$featured_videos = $data_source['jsv_featured_videos'];
		if ($featured_videos == '2') { // only featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NOT NULL ';
		}
		if (!$featured_videos) { // hide featured
			$where .= ' AND f.'.$db->quoteName('cid').' IS NULL ';
		}

		// Guest
		if ($user->guest)
		{

		  	if ($videos_permissions)
			{
			  	$where	.= ' AND a.'.$db->quoteName('permissions').' IN (0,10) ';
			}

		}

		// Member
		if ($user->id)
		{

			// User is member - Permissions are on
		  	if ($videos_permissions) // Only public videos + friends videos + my groups videos + my videos
			{

					$where	.= ' AND (';

					// Get public videos
					$where	.= ' a.'.$db->quoteName('permissions').' IN (0,10,20)';

					// Get my videos
					$where	.= ' OR (a.'.$db->quoteName('creator').' = '.$db->quote($my).' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').')';

					// Get my friends videos
					$my_friends = $this->getFriendIds($my);
					$my_friends_sql = @implode(',', $my_friends);
					if ($my_friends_sql)
					{
						$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('30').' AND a.'.$db->quoteName('creator').' IN ('.$my_friends_sql.'))';
					}

					// Get my groups videos
					/*$my_groups = $this->getGroupIds($my);
					$my_groups_sql = @implode(',', $my_groups);
					if ($my_groups_sql)
					{
						$where	.= ' OR (a.'.$db->quoteName('permissions').' = '.$db->Quote('35').' AND a.'.$db->quoteName('groupid').' IN ('.$my_groups_sql.'))';
					}*/

					$where .= ' ) ';

			// User is member - Permissions are off
			} else {

			  	$where	.= ' AND a.'.$db->quoteName('permissions').' LIKE '.$db->Quote('%').' ';

			}

		}

		// Specific categories
		if (array_key_exists('jsv_videoscategories', $data_source))
		{
			$specific_categories = $data_source['jsv_videoscategories'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$where .= ' AND a.'.$db->quoteName('category_id').' IN ('.$specific_cat.') ';
			}
		}

		// Specific videos
		if ($specific_str && !$specific_categories)
		{
			$where .= ' AND a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude videos
		if (!$specific_str && $excluded_str)
		{
			$where .= ' AND a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.') ';
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $app->input->get('pagination');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$limit = $globalLimit - $start;
				}
			} else {
				$limit = 0;
			}
		}

		// Query
		$query	= 'SELECT a.* FROM '.$db->quoteName( '#__community_videos').' AS a';
		$query	.= ' INNER JOIN '. $db->quoteName( '#__community_videos_category').' AS b';
		$query	.= ' ON a.'.$db->quoteName('category_id').' = b.'.$db->quoteName('id').' ';

		// Featured videos
		if (!$featured_videos || $featured_videos == '2') {
			$query	.= ' LEFT JOIN ' . $db->quoteName( '#__community_featured') . ' AS f';
			$query	.= ' ON a.'.$db->quoteName('id').' = f.'.$db->quoteName('cid').' ';
			$query	.= ' AND f.'.$db->quoteName('type').'=' . $db->Quote( 'videos' );
		}

		$query	.= $where;

		// Ordering
		$ordering = $data_source['jsv_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['jsv_ordering_direction'];
		switch ($ordering)
		{
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'title':
				$query .= ' a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			return $result;

		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}
}
