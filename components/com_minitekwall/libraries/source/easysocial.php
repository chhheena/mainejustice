<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$es = JPATH_ROOT.DS.'components'.DS.'com_easysocial';
if (file_exists($es.DS.'easysocial.php'))
{
	require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php');
}

class MinitekWallLibSourceEasysocial
{
	public function generateIsFriendSQL($source, $target)
	{
		$query = "select count(1) from `#__social_friends` where (`actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";
		return $query;
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

	// Get Easysocial Users
	public function getEasysocialUsers($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$model = ES::model('Users');

		$filter_type = $data_source['esu_filter'];
		$login_only = ($filter_type == 'recent') ? false : true;
		$profile_type = isset($data_source['esu_profileId']) ? $data_source['esu_profileId'] : false;
		$users_without_photo = $data_source['esu_exclude_users_no_photo'];
		//$users_permissions = $data_source['esu_respect_permissions'];

		// Specific users
		$specific_users = $data_source['esu_specific_users'];
		if ($specific_users) {
			$specific_users = trim($specific_users, ',');
			$specific_users = explode(',', $specific_users);
			JArrayHelper::toInteger($specific_users);
			$specific_str = implode(',', $specific_users);
		} else {
			$specific_str = 0;
		}

		// Excluded users
		$exclude_users = $data_source['esu_exclude_users'];
		if ($exclude_users) {
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

		// wheres
		$wheres = array();

		// Enabled users
		$wheres[] = 'a.'.$db->quoteName('block').' = '.$db->quote('0');

		// Determines if we want to filter by logged in users.
		if ($login_only) {
			// Determine if only to fetch front end
			$tmp = 'EXISTS(';
			$tmp .= 'SELECT ' . $db->quoteName('userid') . ' FROM ' . $db->quoteName('#__session') . ' AS f WHERE ' . $db->quoteName('userid') . ' = a.' . $db->quoteName('id');
			$tmp .= ')';

			$wheres[] = $tmp;
		}

		// Show users from specific profile types
		if ($profile_type) {
			$profile = ES::makeArray($profile_type);
			$wheres[] = "e.`profile_id` IN (" . implode(',', $profile) . ")";
		}

		// Specific users
		if ($specific_str)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude users
		if (!$specific_str && $excluded_str)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.') ';
		}

		// lets check for the avatar validity here.
		if (!$users_without_photo) {
			$tmp = "exists (select `photo_id` from `#__social_photos_meta` as pm where pm.`photo_id` = g.`photo_id`";
			$tmp .= " and pm.`group` = " . $db->Quote('path');
			$tmp .= " and pm.`property` = " . $db->Quote('large');
			$tmp .= ")";

			$wheres[] = $tmp;
		}

		// Query
		$query = "select a.`id`, a.`registerDate`, b.`type`";
		$query .= " FROM `#__users` as a";
		$query .= " INNER JOIN `#__social_users` as b on a.`id` = b.`user_id`";

		// Show friends only
		if ($filter_type == 'onlinefriends') {
			$query .= " INNER JOIN `#__social_friends` as ff on a.`id` = if(ff.`target_id` = " . $db->Quote($my). ", ff.`actor_id`, ff.`target_id`)";
		}

		// Join with the social profiles table
		if ($profile_type) {
			$query .= " INNER JOIN `#__social_profiles_maps` as e on e.`user_id` = a.`id`";
		}

		// Users with avatar only
		if (!$users_without_photo) {
			// There is an instance where the user id is the same as cluster id. So, that is why we need to specify the avatar type.
			$query .= " INNER JOIN `#__social_avatars` as g ON g.`uid` = a.`id` and g.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and g.`small` != ''";
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = " WHERE ";
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		// glue the main query with where conditions.
		$query .= $where;

		// Ordering
		$ordering = $data_source['esu_ordering'];
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['esu_ordering_direction'];
		switch ($ordering)
		{
			case 'registerDate':
				$query .= ' a.'.$db->quoteName('registerDate').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'id':
				$query .= ' a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'username':
				$query .= ' a.'.$db->quoteName('username').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'name':
				$query .= ' a.'.$db->quoteName('name').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'lastvisitDate':
				$query .= ' a.'.$db->quoteName('lastvisitDate').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default :
			    $query .= ' a.'.$db->quoteName('registerDate').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
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

		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();
		}

		return $result;
	}

	// Get Easysocial Groups
	public function getEasysocialGroups($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$model = ES::model('Groups');

		$groups_without_photo = $data_source['esg_exclude_groups_no_photo'];
		$featured_groups = $data_source['esg_featured_groups'];
	  	$groups_hide_private = $data_source['esg_hide_private'];
		$ordering = $data_source['esg_ordering'];

		// Specific groups
		$specific_groups = $data_source['esg_specific_groups'];
		if ($specific_groups) {
			$specific_groups = trim($specific_groups, ',');
			$specific_groups = explode(',', $specific_groups);
			JArrayHelper::toInteger($specific_groups);
			$specific_str = implode(',', $specific_groups);
		} else {
			$specific_str = 0;
		}

		// Excluded groups
		$exclude_groups = $data_source['esg_exclude_groups'];
		if ($exclude_groups) {
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

		// wheres
		$wheres = array();

		// Published groups
		$wheres[] = 'a.'.$db->quoteName('state').' = '.$db->quote('1');

		// Type is group
		$wheres[] = 'a.'.$db->quoteName('cluster_type').' = '.$db->Quote(SOCIAL_TYPE_GROUP);

		// Specific categories
		$specific_categories = false;
		if (array_key_exists('esg_category', $data_source))
		{
			$specific_categories = $data_source['esg_category'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$wheres[] = 'a.'.$db->quoteName('category_id').' IN ('.$specific_cat.') ';
			}
		}

		// Specific groups
		if ($specific_str && !$specific_categories)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude groups
		if (!$specific_str && $excluded_str)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.')';
		}

		// lets check for the avatar validity here.
		if (!$groups_without_photo) {
			$tmp = "exists (select `photo_id` from `#__social_photos_meta` as pm where pm.`photo_id` = g.`photo_id`";
			$tmp .= " and pm.`group` = " . $db->Quote('path');
			$tmp .= " and pm.`property` = " . $db->Quote('large');
			$tmp .= ")";

			$wheres[] = $tmp;
		}

		// Featured groups
		if ($featured_groups == '2') { // only featured
			$wheres[] = 'a.`featured`=' . $db->Quote('1');
		}
		if (!$featured_groups) { // hide featured
			$wheres[] = 'a.`featured`=' . $db->Quote('0');
		}

		// Guest
		if ($user->guest)
		{
			if ($groups_hide_private) // Only public groups
			{
				$wheres[] = 'a.`type` = '.$db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE);
			}
		}

		// Member
		if ($my)
		{
		  	if ($groups_hide_private) // Only public groups	+ my groups
			{
			  	$tmp = '(';
				$tmp .= 'a.`type` = '.$db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE);
				$tmp .= ' OR';
				$tmp .= " (a.`type` > 1 and (select count(*) from `#__social_clusters_nodes` as aa where aa.`cluster_id` = a.`id` and aa.`uid` = $my) > 0)";
				$tmp .= ')';

				$wheres[] = $tmp;
			}
		}

		// Query
		$query = 'SELECT DISTINCT `a`.* FROM `#__social_clusters` AS `a`';

		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
			$query .= ' ON a.`creator_uid` = bus.`user_id`';
			$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query .= " AND `bus`.`id` IS NULL";
		}

		//if ($ordering == 'members') {
			$query .= ' INNER JOIN `#__social_clusters_nodes` AS f';
			$query .= ' ON f.`cluster_id` = a.`id`';
			$query .= ' AND f.`state`=' . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);
		//}

		// Groups with avatar only
		if (!$groups_without_photo) {
			// There is an instance where the group id is the same as cluster id. So, that is why we need to specify the avatar type.
			$query .= " INNER JOIN `#__social_avatars` as g ON g.`uid` = a.`id` and g.`type` = " . $db->Quote(SOCIAL_TYPE_GROUP) . " and g.`small` != ''";
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = " WHERE ";
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		// glue the main query with where conditions.
		$query .= $where;

		$query .= ' GROUP BY f.'.$db->quoteName('id');

		// Ordering
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['esg_ordering_direction'];
		switch ($ordering)
		{
			case 'latest':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'title':
				$query .= ' a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'popular':
				$query .= ' a.'.$db->quoteName('hits').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'members':
				$query .= ' COUNT(f.`id`), a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default:
			    $query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
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

		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();
		}

		return $result;
	}

	// Get Easysocial Events
	public function getEasysocialEvents($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$model = ES::model('Events');

		$events_without_photo = $data_source['ese_exclude_events_no_photo'];
		$featured_events = $data_source['ese_featured_events'];
	  	$events_hide_private = $data_source['ese_events_hide_private'];
		$ordering = $data_source['ese_ordering'];

		// Specific events
		$specific_events = $data_source['ese_specific_events'];
		if ($specific_events) {
			$specific_events = trim($specific_events, ',');
			$specific_events = explode(',', $specific_events);
			JArrayHelper::toInteger($specific_events);
			$specific_str = implode(',', $specific_events);
		} else {
			$specific_str = 0;
		}

		// Excluded events
		$exclude_events = $data_source['ese_exclude_events'];
		if ($exclude_events) {
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

		// wheres
		$wheres = array();

		// Published events
		$wheres[] = 'a.'.$db->quoteName('state').' = '.$db->quote('1');

		// Type is event
		$wheres[] = 'a.'.$db->quoteName('cluster_type').' = '.$db->Quote(SOCIAL_TYPE_EVENT);

		// Specific categories
		$specific_categories = false;
		if (array_key_exists('ese_category', $data_source))
		{
			$specific_categories = $data_source['ese_category'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat = implode(',', $specific_categories);
				$wheres[] = ' AND a.'.$db->quoteName('category_id').' IN ('.$specific_cat.') ';
			}
		}

		// Specific groups
		$specific_groups = false;
		if (array_key_exists('ese_groupId', $data_source)) {
			$specific_groups = $data_source['ese_groupId'];
			if ($specific_groups)
			{
				JArrayHelper::toInteger($specific_groups);
				$specific_groups_str = implode(',', $specific_groups);
				$wheres[] = ' b.'.$db->quoteName('group_id').' IN ('.$specific_groups_str.') ';
			}
		}

		// Specific events
		if ($specific_str && !$specific_categories)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' IN ('.$specific_str.') ';
		}

		// Exclude events
		if (!$specific_str && $excluded_str)
		{
			$wheres[] = 'a.'.$db->quoteName('id').' NOT IN ('.$excluded_str.')';
		}

		// lets check for the avatar validity here.
		if (!$events_without_photo) {
			$tmp = "exists (select `photo_id` from `#__social_photos_meta` as pm where pm.`photo_id` = g.`photo_id`";
			$tmp .= " and pm.`group` = " . $db->Quote('path');
			$tmp .= " and pm.`property` = " . $db->Quote('large');
			$tmp .= ")";

			$wheres[] = $tmp;
		}

		// Featured events
		if ($featured_events == '2') { // only featured
			$wheres[] = 'a.`featured`=' . $db->Quote('1');
		}
		if (!$featured_events) { // hide featured
			$wheres[] = 'a.`featured`=' . $db->Quote('0');
		}

		// Time filter
		$time_filter = $data_source['ese_time_filter'];
		$now = ES::date()->toSql(true);
		if ($time_filter == 'all') {
			$temp = "(";
			$temp .= "(`b`.`end` != '0000-00-00 00:00:00' AND `b`.`end` > " . $db->q($now) . ")";
			$temp .= " OR (`b`.`end` = '0000-00-00 00:00:00')";
			$temp .= ")";

			$wheres[] = $temp;
		}
		if ($time_filter == 'past') {
			$wheres[] = "(`b`.`end` != '0000-00-00 00:00:00' AND `b`.`end` < " . $db->q($now) . ")";
		}
		if ($time_filter == 'ongoing') {
			$wheres[] = "`b`.`start` <= " . $db->q($now);
			$wheres[] = "(`b`.`end` = '0000-00-00 00:00:00' OR `b`.`end` >= " . $db->q($now) . ")";
		}
		if ($time_filter == 'upcoming') {
			$wheres[] = "`b`.`start` >= " . $db->q($now);
		}

		// Guest
		if ($user->guest)
		{
			if ($events_hide_private) // Only public events
			{
				$wheres[] = 'a.`type` = '.$db->Quote(SOCIAL_EVENT_TYPE_PUBLIC);
			}
		}

		// Member
		if ($my)
		{
		  	if ($events_hide_private) // Only public events	+ my events
			{
			  	$tmp = '(';
				$tmp .= 'a.`type` = '.$db->Quote(SOCIAL_EVENT_TYPE_PUBLIC);
				$tmp .= ' OR';
				$tmp .= " (a.`type` > 1 and (select count(*) from `#__social_clusters_nodes` as aa where aa.`cluster_id` = a.`id` and aa.`uid` = $my) > 0)";
				$tmp .= ')';

				$wheres[] = $tmp;
			}
		}

		// Query
		$query = 'SELECT DISTINCT `a`.*, `b`.`start` AS startdate, `b`.`end` AS enddate FROM `#__social_clusters` AS `a`';
		$query .= " LEFT JOIN `#__social_events_meta` AS `b` ON `a`.`id` = `b`.`cluster_id`";

		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
			$query .= ' ON a.`creator_uid` = bus.`user_id`';
			$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query .= " AND `bus`.`id` IS NULL";
		}

		// Events with avatar only
		if (!$events_without_photo) {
			// There is an instance where the event id is the same as cluster id. So, that is why we need to specify the avatar type.
			$query .= " INNER JOIN `#__social_avatars` as g ON g.`uid` = a.`id` and g.`type` = " . $db->Quote(SOCIAL_TYPE_EVENT) . " and g.`small` != ''";
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = " WHERE ";
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		// glue the main query with where conditions.
		$query .= $where;

		// Ordering
		$query .= ' ORDER BY ';
		$ordering_dir = $data_source['ese_ordering_direction'];
		switch ($ordering)
		{
			case 'start':
				$query .= ' b.'.$db->quoteName('start').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'end':
				$query .= ' b.'.$db->quoteName('end').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'name':
				$query .= ' a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query .= ' a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query	.= ' RAND() ';
				break;
			default:
			    $query .= ' a.'.$db->quoteName('start').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
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

		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();
		}

		return $result;
	}

	// Get Easysocial Photos
	public function getEasysocialPhotos($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$include_avatars = $data_source['esp_include_avatars'];
		$include_covers = $data_source['esp_include_covers'];
		$photos_permissions = $data_source['esp_respect_permissions'];

		// Specific users
		$specific_users = $data_source['esp_specific_users'];
		if ($specific_users) {
			$specific_users = trim($specific_users, ',');
			$specific_users = explode(',', $specific_users);
			JArrayHelper::toInteger($specific_users);
			$specific_users_str = implode(',', $specific_users);
		} else {
			$specific_users_str = 0;
		}

		// Exclude users
		$exclude_users = $data_source['esp_exclude_users'];
		if ($exclude_users) {
			$exclude_users = trim($exclude_users, ',');
			$exclude_users = explode(',', $exclude_users);
			JArrayHelper::toInteger($exclude_users);
			$exclude_users_str = implode(',', $exclude_users);
		} else {
			$exclude_users_str = 0;
		}

		// Specific albums
		$specific_albums = $data_source['esp_specific_albums'];
		if ($specific_albums) {
			$specific_albums = trim($specific_albums, ',');
			$specific_albums = explode(',', $specific_albums);
			JArrayHelper::toInteger($specific_albums);
			$specific_albums_str = implode(',', $specific_albums);
		} else {
			$specific_albums_str = 0;
		}

		// Exclude albums
		$exclude_albums = $data_source['esp_exclude_albums'];
		if ($exclude_albums) {
			$exclude_albums = trim($exclude_albums, ',');
			$exclude_albums = explode(',', $exclude_albums);
			JArrayHelper::toInteger($exclude_albums);
			$exclude_albums_str = implode(',', $exclude_albums);
		} else {
			$exclude_albums_str = 0;
		}

		// Specific photos
		$specific_photos = $data_source['esp_specific_photos'];
		if ($specific_photos) {
			$specific_photos = trim($specific_photos, ',');
			$specific_photos = explode(',', $specific_photos);
			JArrayHelper::toInteger($specific_photos);
			$specific_photos_str = implode(',', $specific_photos);
		} else {
			$specific_photos_str = 0;
		}

		// Exclude photos
		$exclude_photos = $data_source['esp_exclude_photos'];
		if ($exclude_photos) {
			$exclude_photos = trim($exclude_photos, ',');
			$exclude_photos = explode(',', $exclude_photos);
			JArrayHelper::toInteger($exclude_photos);
			$exclude_photos_str = implode(',', $exclude_photos);
		} else {
			$exclude_photos_str = 0;
		}

		$user = JFactory::getUser();
		$my = $user->id;
		$db = JFactory::getDBO();
		$config = ES::config();
		$viewer = $my;

		// Query
		$query = array();

		$accessColumn = "(select pri.value as `access` from `#__social_privacy_items` as pri";
		$accessColumn .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid=a.id and pri.`type` = 'photos'";
		$accessColumn .= " UNION ALL ";
		$accessColumn .= " select prm.value as `access`";
		$accessColumn .= " from `#__social_privacy_map` as prm";
		$accessColumn .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessColumn .= "	left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
		$accessColumn .= " where prm.uid = a.user_id and prm.utype = 'user'";
		$accessColumn .= "	and pp.type = 'photos' and pp.rule = 'view'";
		$accessColumn .= " union all ";
		$accessColumn .= " select prm.value as `access`";
		$accessColumn .= " from `#__social_privacy_map` as prm";
		$accessColumn .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessColumn .= "	inner join `#__social_profiles_maps` pmp on prm.uid = pmp.profile_id";
		$accessColumn .= " where prm.utype = 'profiles' and pmp.user_id = a.user_id";
		$accessColumn .= "	and pp.type = 'photos' and pp.rule = 'view'";
		$accessColumn .= " limit 1";
		$accessColumn .= ") as es_access";

		$accessCustomColumn = "(select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access` from `#__social_privacy_items` as pri";
		$accessCustomColumn .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid=a.id and pri.`type` = 'photos'";
		$accessCustomColumn .= " UNION ALL ";
		$accessCustomColumn .= " select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access`";
		$accessCustomColumn .= " from `#__social_privacy_map` as prm";
		$accessCustomColumn .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessCustomColumn .= "	left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
		$accessCustomColumn .= " where prm.uid = a.user_id and prm.utype = 'user'";
		$accessCustomColumn .= "	and pp.type = 'photos' and pp.rule = 'view'";
		$accessCustomColumn .= " limit 1";
		$accessCustomColumn .= ") as es_custom_access";

		$accessFieldColumn = "(select `field_access` from `#__social_privacy_items` as pri where pri.`uid`=a.`id` and pri.`type` = 'photos') as field_access";

		$accessColumnAlbum = "(select pri.value as `access` from `#__social_privacy_items` as pri";
		$accessColumnAlbum .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid=a.album_id and pri.`type` = 'albums'";
		$accessColumnAlbum .= " UNION ALL ";
		$accessColumnAlbum .= " select prm.value as `access`";
		$accessColumnAlbum .= " from `#__social_privacy_map` as prm";
		$accessColumnAlbum .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessColumnAlbum .= "	left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
		$accessColumnAlbum .= " where prm.uid = a.user_id and prm.utype = 'user'";
		$accessColumnAlbum .= "	and pp.type = 'albums' and pp.rule = 'view'";
		$accessColumnAlbum .= " union all ";
		$accessColumnAlbum .= " select prm.value as `access`";
		$accessColumnAlbum .= " from `#__social_privacy_map` as prm";
		$accessColumnAlbum .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessColumnAlbum .= "	inner join `#__social_profiles_maps` pmp on prm.uid = pmp.profile_id";
		$accessColumnAlbum .= " where prm.utype = 'profiles' and pmp.user_id = a.user_id";
		$accessColumnAlbum .= "	and pp.type = 'albums' and pp.rule = 'view'";
		$accessColumnAlbum .= " limit 1";
		$accessColumnAlbum .= ") as accessAlbum";

		$accessCustomColumnAlbum = "(select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access` from `#__social_privacy_items` as pri";
		$accessCustomColumnAlbum .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid=a.album_id and pri.`type` = 'albums'";
		$accessCustomColumnAlbum .= " UNION ALL ";
		$accessCustomColumnAlbum .= " select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access`";
		$accessCustomColumnAlbum .= " from `#__social_privacy_map` as prm";
		$accessCustomColumnAlbum .= "	inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
		$accessCustomColumnAlbum .= "	left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
		$accessCustomColumnAlbum .= " where prm.uid = a.user_id and prm.utype = 'user'";
		$accessCustomColumnAlbum .= "	and pp.type = 'albums' and pp.rule = 'view'";
		$accessCustomColumnAlbum .= " limit 1";
		$accessCustomColumnAlbum .= ") as custom_accessAlbum";

		$accessFieldColumnAlbum = "(select `field_access` from `#__social_privacy_items` as pri where pri.`uid`=a.`album_id` and pri.`type` = 'albums') as field_accessAlbum";

		if ($photos_permissions && $config->get('privacy.enabled')) {
			$query[] = "select * from (";
		}

		$query[] = "select a.*";

		// user privacy
		if ($photos_permissions && $config->get('privacy.enabled')) {
			$query[] = ", $accessColumn, $accessCustomColumn";
			$query[] = ", $accessColumnAlbum, $accessCustomColumnAlbum";

			if ($config->get('users.privacy.field')) {
				$query[] = ", $accessFieldColumn, $accessFieldColumnAlbum";
			}
		}

		$query[] = "from `#__social_photos` as a";

		if (!$include_avatars || !$include_covers) {
			$query[] = " inner join `#__social_albums` as b on a.album_id = b.id";
		}

		// cluster privacy
		if ($photos_permissions) {
			// join with cluster table.
			$query[] = " left join `#__social_clusters` as cc on a.`uid` = cc.`id` and a.`type` = cc.`cluster_type`";
			if ($viewer) {
				$query[] = " left join `#__social_events_meta` AS em ON cc.`id` = em.`cluster_id`";
			}
		}

		$query[] = "where a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		// Specific users
		if ($specific_users_str)
		{
			$query[] = 'AND a.'.$db->quoteName('uid').' IN ('.$specific_users_str.') ';
		}

		// Exclude users
		if (!$specific_users_str && $exclude_users_str)
		{
			$query[] = 'AND a.'.$db->quoteName('uid').' NOT IN ('.$exclude_users_str.')';
		}

		// Specific albums
		if ($specific_albums_str)
		{
			$query[] = 'AND a.'.$db->quoteName('album_id').' IN ('.$specific_albums_str.') ';
		}

		// Exclude albums
		if (!$specific_albums_str && $exclude_albums_str)
		{
			$query[] = 'AND a.'.$db->quoteName('album_id').' NOT IN ('.$exclude_albums_str.')';
		}

		// Specific photos
		if ($specific_photos_str)
		{
			$query[] = 'AND a.'.$db->quoteName('id').' IN ('.$specific_photos_str.') ';
		}

		// Exclude photos
		if (!$specific_photos_str && $exclude_photos_str)
		{
			$query[] = 'AND a.'.$db->quoteName('id').' NOT IN ('.$exclude_photos_str.')';
		}

		// Featured photos
		$featured_photos = $data_source['esp_featured_photos'];
		if ($featured_photos == '2') { // only featured
			$query[] = 'AND a.'.$db->quoteName('featured').' = ' . $db->Quote('1');
		}
		if (!$featured_photos) { // hide featured
			$query[] = 'AND a.'.$db->quoteName('featured').' = ' . $db->Quote('0');
		}

		// Include avatars/covers
		if (!$include_avatars && !$include_covers) {
			$query[] = " and b.`core` not in (1, 2)";
		} else if (!$include_avatars) {
			$query[] = " and b.`core` != 1";
		} else if (!$include_covers) {
			$query[] = " and b.`core` != 2";
		}

		if ($photos_permissions) {
			// cluster privacy
			$query[] = 'AND (';
			$query[]	= '(a.`type` = ' . $db->Quote(SOCIAL_TYPE_USER) . ') OR';
			$query[]	= '(a.`type` != ' . $db->Quote(SOCIAL_TYPE_USER) . ' and cc.`type` = 1)';

			if ($viewer) {
				$query[]	= 'OR (a.`type` != ' . $db->Quote(SOCIAL_TYPE_USER) . ' and cc.`type` > 1 and ' . $viewer . ' IN (select scn.`uid` from `#__social_clusters_nodes` as scn where (scn.`cluster_id` = cc.`id` OR scn.`cluster_id` = em.`group_id`) and scn.`type` = ' . $db->Quote(SOCIAL_TYPE_USER) . ' and scn.`state` = 1))';
			}

			$query[]	= ')';
		}

		$query[] = 'GROUP BY a.'.$db->quoteName('id');

		// Ordering
		$ordering = $data_source['esp_ordering'];
		$ordering_dir = $data_source['esp_ordering_direction'];
		switch ($ordering)
		{

			case 'title':
				$query[] = 'ORDER BY a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query[] = 'ORDER BY  a.'.$db->quoteName('created').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query[] = 'ORDER BY  RAND() ';
				break;
			default:
			    $query[] = 'ORDER BY  a.'.$db->quoteName('title').' '.$ordering_dir.', a.'.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		// privacy checking end here.
		if ($photos_permissions && $config->get('privacy.enabled')) {

			$query[] = ") as x";

			// privacy here.
			$query[] = ' WHERE (';

			//public
			$query[] = '(x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('x.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (x.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (x.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '  )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = x.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('photos');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (x.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			// my own items.
			$query[] = '(x.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';

			// album privacy
			$query[] = ' AND (';

			//public
			$query[] = '(x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('x.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (x.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (x.`custom_accessAlbum` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '  )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = x.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('albums');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (x.`field_accessAlbum` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((x.`accessAlbum` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}


			// my own items.
			$query[] = '(x.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';
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

		$query = implode(' ', $query);
		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result) {
				return $result;
			}

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			$photos = array();

			foreach ($result as $row) {
				$photo = ES::table('Photo');
				$photo->bind($row);

				$photos[] = $photo;
			}

			return $photos;
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();

			return $result;
		}
	}

	// Get Easysocial Albums
	public function getEasysocialAlbums($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$include_avatars = $data_source['esa_include_avatars'];
		$include_covers = $data_source['esa_include_covers'];
		$albums_permissions = $data_source['esa_respect_permissions'];
		$ordering = $data_source['esa_ordering'];

		// Specific users
		$specific_users = $data_source['esa_specific_users'];
		if ($specific_users) {
			$specific_users = trim($specific_users, ',');
			$specific_users = explode(',', $specific_users);
			JArrayHelper::toInteger($specific_users);
			$specific_users_str = implode(',', $specific_users);
		} else {
			$specific_users_str = 0;
		}

		// Exclude users
		$exclude_users = $data_source['esa_exclude_users'];
		if ($exclude_users) {
			$exclude_users = trim($exclude_users, ',');
			$exclude_users = explode(',', $exclude_users);
			JArrayHelper::toInteger($exclude_users);
			$exclude_users_str = implode(',', $exclude_users);
		} else {
			$exclude_users_str = 0;
		}

		// Specific albums
		$specific_albums = $data_source['esa_specific_albums'];
		if ($specific_albums) {
			$specific_albums = trim($specific_albums, ',');
			$specific_albums = explode(',', $specific_albums);
			JArrayHelper::toInteger($specific_albums);
			$specific_albums_str = implode(',', $specific_albums);
		} else {
			$specific_albums_str = 0;
		}

		// Exclude albums
		$exclude_albums = $data_source['esa_exclude_albums'];
		if ($exclude_albums) {
			$exclude_albums = trim($exclude_albums, ',');
			$exclude_albums = explode(',', $exclude_albums);
			JArrayHelper::toInteger($exclude_albums);
			$exclude_albums_str = implode(',', $exclude_albums);
		} else {
			$exclude_albums_str = 0;
		}

		$my = ES::user();
		$db = JFactory::getDBO();
		$config = ES::config();
		$streamLib = ES::stream();

		// Query
		$query = 'select * from (';
		$query .= 'select a.*';

		if ($config->get('privacy.enabled') && $albums_permissions) {
			$privacyColumn = "(select pi.value from `#__social_privacy_items` as pi";
			$privacyColumn .= " where pi.`type` = 'albums' and pi.uid = a.id and pi.user_id = a.user_id";
			$privacyColumn .= " union all";
			$privacyColumn .= " select pm.value from `#__social_privacy_map` as pm";
			$privacyColumn .= " inner join #__social_privacy as pp on pm.privacy_id = pp.id";
			$privacyColumn .= " where pm.utype = 'user' and pm.uid = a.user_id and pp.type = 'albums' limit 1";
			$privacyColumn .= " ) as es_access";

			$query .= ", $privacyColumn";

			if ($config->get('users.privacy.field')) {
				$privacyFieldColumn = "(select ifnull ((select `field_access` from `#__social_privacy_items` as pri where pri.`uid` = a.`id` and pri.`type` = 'albums'), 0)) as es_field_access";

				$query .= ", $privacyFieldColumn";
			}

			$customAccessColumn = "(select pc.`user_id` from `#__social_privacy_items` as pi";
			$customAccessColumn .= " INNER JOIN `#__social_privacy_customize` as pc on pc.`uid` = pi.`id`";
			$customAccessColumn .= " WHERE pi.`uid` = a.id AND pi.`type` = 'albums' AND pc.`user_id` = " . $db->Quote($my->id);
			$customAccessColumn .= ") as es_custom_access";

			$query .= ", $customAccessColumn";
		}

		$query .= ' from `#__social_albums` as a';

		// We don't want albums that belongs to blocked user being displayed
		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			// user block
			$query .= ' LEFT JOIN ' . $db->quoteName('#__social_block_users') . ' as bus';
			$query .= ' ON a.' . $db->quoteName('user_id') . ' = bus.' . $db->quoteName('user_id') ;
			$query .= ' AND bus.' . $db->quoteName('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
		}

		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= ' AND bus.' . $db->quoteName('id') . ' IS NULL';
		}

		if (isset($options['excludedisabled']) && $options['excludedisabled']) {
			$query .= ' INNER JOIN `#__users` AS uu ON uu.`id` = a.' . $db->quoteName('user_id') . ' AND uu.`block` = ' . $db->Quote('0');
		}

		$wheres = array();
		$wrapWheres = array();

		// Specific users
		if (!$specific_albums_str && $specific_users_str)
		{
			$wrapWheres[] = ' a.'.$db->quoteName('user_id').' IN ('.$specific_users_str.') ';
		}

		// Exclude users
		if (!$specific_users_str && $exclude_users_str)
		{
			$wrapWheres[] = ' a.'.$db->quoteName('user_id').' NOT IN ('.$exclude_users_str.') ';
		}

		// Specific albums
		if ($specific_albums_str)
		{
			$wrapWheres[] = ' a.'.$db->quoteName('id').' IN ('.$specific_albums_str.') ';
		}

		// Exclude albums
		if (!$specific_albums_str && $exclude_albums_str)
		{
			$wrapWheres[] = ' a.'.$db->quoteName('id').' NOT IN ('.$exclude_albums_str.') ';
		}

		// Avatar albums
		if (!$include_avatars) {
			$wrapWheres[] = ' a.`core` != ' . $db->Quote('1');
		}

		// Cover albums
		if (!$include_covers) {
			$wrapWheres[] = ' a.`core` != ' . $db->Quote('2');
		}

		$wrapWhere = '';

		if (count($wrapWheres) > 0) {
			$wrapWhere = ' where ';
			$wrapWhere .= (count($wrapWheres) == 1) ? $wrapWheres[0] : implode(' and ', $wrapWheres);
		}

		$query .= $wrapWhere;
		$query .= ') as albums ';

		// Check for albums privacy
		if ($config->get('privacy.enabled') && $albums_permissions && !$my->isSiteAdmin()) {

			// privacy start here.
			$privacyQuery = ' (';

			// public
			$privacyQuery .= ' (albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR (albums.`access` IS NULL) OR ';

			// member
			$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $my->id . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				// friends of friends
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($my->id, 'albums.`user_id`') . ') > 0)) OR ';

				// friends of friends
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('albums.`user_id`', $my->id) . ') > 0)) OR ';

				// friends
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('albums.`user_id`', $my->id) . ') > 0)) OR ';
			} else {
				// fall back to member
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
			}

			// only me
			$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (albums.`user_id` = ' . $my->id . ')) OR ';

			// custom
			$privacyQuery .= '(albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ' AND albums.`custom_access` = ' . $db->Quote($my->id) . '  ) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fa.`uid` = albums.`id`';
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($my->id);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (albums.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$privacyQuery .= ' ((albums.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $my->id . ' > 0)) OR ';
			}

			// viewer items
			$privacyQuery .= ' (albums.`user_id` = ' . $my->id . ')';

			// privacy ended here
			$privacyQuery .= ')';

			// Additional privacy query for clusters privacy
			$clusterPrivacyQuery = ' (';

			// Check if the cluster is private and current viewer is a member of this cluster
			$clusterPrivacyQuery .= ' albums.`uid` NOT IN(';
			$clusterPrivacyQuery .= ' select sc.`id` from `#__social_clusters` as sc';
			$clusterPrivacyQuery .= ' WHERE (';
			$clusterPrivacyQuery .= ' sc.`type` NOT IN(' . $db->Quote(1) . ', ' . $db->Quote(4) . ')';
			$clusterPrivacyQuery .= ' AND ' . $db->Quote($my->id) . ' NOT IN(';
			$clusterPrivacyQuery .= ' select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = sc.`id` and scn.`state` = ' . $db->Quote(1) . ')))';

			// End of cluster privacy
			$clusterPrivacyQuery .= ')';

			$wheres[] = $privacyQuery;
			$wheres[] = $clusterPrivacyQuery;
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query .= $where;

		// Ordering
		$orderby = ' ORDER BY';
		$ordering_dir = $data_source['esa_ordering_direction'];
		switch ($ordering)
		{
			case 'title':
				$orderby .= ' '.$db->quoteName('title').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$orderby .= ' '.$db->quoteName('created').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'hits':
				$orderby .= ' '.$db->quoteName('hits').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$orderby	.= ' RAND() ';
				break;
			default :
				$orderby .= ' '.$db->quoteName('title').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$query .= $orderby;

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

		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			foreach ($result as $row) {
				$album = ES::table('Album');
				$album->bind($row);

				$albums[] = $album;
			}

			return $albums;
		}
		else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();

			return $result;
		}
	}

	// Get Easysocial Videos
	public function getEasysocialVideos($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$featured_videos = $data_source['esv_featured_videos'];
		$videos_permissions = $data_source['esv_respect_permissions'];
		$ordering = $data_source['esv_ordering'];

		// Specific users
		$specific_users = $data_source['esv_specific_users'];
		if ($specific_users) {
			$specific_users = trim($specific_users, ',');
			$specific_users = explode(',', $specific_users);
			JArrayHelper::toInteger($specific_users);
			$specific_users_str = implode(',', $specific_users);
		} else {
			$specific_users_str = 0;
		}

		// Exclude users
		$exclude_users = $data_source['esv_exclude_users'];
		if ($exclude_users) {
			$exclude_users = trim($exclude_users, ',');
			$exclude_users = explode(',', $exclude_users);
			JArrayHelper::toInteger($exclude_users);
			$exclude_users_str = implode(',', $exclude_users);
		} else {
			$exclude_users_str = 0;
		}

		// Specific videos
		$specific_videos = $data_source['esv_specific_videos'];
		if ($specific_videos) {
			$specific_videos = trim($specific_videos, ',');
			$specific_videos = explode(',', $specific_videos);
			JArrayHelper::toInteger($specific_videos);
			$specific_videos_str = implode(',', $specific_videos);
		} else {
			$specific_videos_str = 0;
		}

		// Exclude videos
		$exclude_videos = $data_source['esv_exclude_videos'];
		if ($exclude_videos) {
			$exclude_videos = trim($exclude_videos, ',');
			$exclude_videos = explode(',', $exclude_videos);
			JArrayHelper::toInteger($exclude_videos);
			$exclude_videos_str = implode(',', $exclude_videos);
		} else {
			 $exclude_videos_str = 0;
		}

		$my = ES::user();
		$db = JFactory::getDBO();
		$config = ES::config();
		$isSiteAdmin = ES::user()->isSiteAdmin();

		$accessColumn = $this->getAccessColumn('access', 'a');
		$accessCustomColumn = $this->getAccessColumn('customaccess', 'a');
		$accessFieldColumn = $this->getAccessColumn('fieldaccess', 'a');

		// Query
		$query = array();

		if (!$isSiteAdmin && $videos_permissions) {
			$query[] = "select * from (";
		}

		$query[] = "select a.*";

		if (!$isSiteAdmin && $videos_permissions) {
			$query[] = ", $accessColumn, $accessCustomColumn";

			if ($config->get('users.privacy.field')) {
				$query[] = ", $accessFieldColumn";
			}
		}

		$orderTblPrefix = 'a';

		$query[] = "from `#__social_videos` as a";

		// Published
		$query[] = "where a.`state` = " . $db->Quote(SOCIAL_VIDEO_PUBLISHED);

		// Specific categories
		$specific_categories = false;
		if (array_key_exists('esv_category', $data_source))
		{
			$specific_categories = $data_source['esv_category'];
			if ($specific_categories)
			{
				JArrayHelper::toInteger($specific_categories);
				$specific_cat_str = implode(',', $specific_categories);
				$query[] = 'AND a.'.$db->quoteName('category_id').' IN ('.$specific_cat_str.') ';
			}
		}

		// Specific videos
		if ($specific_videos_str && !$specific_categories)
		{
			$query[] = 'AND a.'.$db->quoteName('id').' IN ('.$specific_videos_str.') ';
		}

		// Exclude videos
		if (!$specific_videos_str && $exclude_videos_str)
		{
			$query[] = 'AND a.'.$db->quoteName('id').' NOT IN ('.$exclude_videos_str.') ';
		}

		// Specific users
		if ($specific_users_str && !$specific_videos_str)
		{
			$query[] = 'AND a.'.$db->quoteName('user_id').' IN ('.$specific_users_str.') ';
		}

		// Exclude users
		if (!$specific_users_str && $exclude_users_str)
		{
			$query[] = 'AND a.'.$db->quoteName('user_id').' NOT IN ('.$exclude_users_str.') ';
		}

		// Featured videos
		if ($featured_videos == '2') { // only featured
			$query[] = "and a.`featured` = " . $db->Quote('1');
		}
		if (!$featured_videos) { // hide featured
			$query[] = "and a.`featured` = " . $db->Quote('0');
		}

		if (!$isSiteAdmin && $videos_permissions) {
			$orderTblPrefix = 'x';

			$viewer = FD::user()->id;

			$query[] = ") as x";

			if ($config->get('privacy.enabled')) {
				// privacy here.
				$query[] = " WHERE (";

				//public
				$query[] = "(x.`access` = " . $db->Quote( SOCIAL_PRIVACY_PUBLIC ) . ") OR";

				//member
				$query[] = "( (x.`access` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND (" . $viewer . " > 0 ) ) OR ";

				if ($config->get('friends.enabled')) {
					//friends
					$query[] = "( (x.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ( (" . $this->generateIsFriendSQL( 'x.`user_id`', $viewer ) . ") > 0 ) ) OR ";
				} else {
					// fall back to member
					$query[] = "( (x.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND (" . $viewer . " > 0 ) ) OR ";
				}

				//only me
				$query[] = "( (x.`access` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND ( x.`user_id` = " . $viewer . " ) ) OR ";

				// custom
				$query[] = "( (x.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( x.`custom_access` LIKE " . $db->Quote( '%,' . $viewer . ',%' ) . "    ) ) OR ";

				// field
				if ($config->get('users.privacy.field')) {
					// field
					$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
					$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
					$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
					$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
					$fieldPrivacyQuery .= ' where fi.`uid` = x.`id`';
					$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('videos');
					$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
					$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
					$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

					$query[] = "((x.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (x.`field_access` <= " . $fieldPrivacyQuery . ")) OR ";
				} else {
					$query[] = " ((x.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (" . $viewer . " > 0)) OR ";
				}

				// my own items.
				$query[] = "(x.`user_id` = " . $viewer . ")";

				// privacy checking end here.
				$query[] = ")";
			}
		}

		// Ordering
		$query[] = 'ORDER BY ';
		$ordering_dir = $data_source['esv_ordering_direction'];
		switch ($ordering)
		{
			case 'title':
				$query[] = ' '.$db->quoteName('title').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'created':
				$query[] = ' '.$db->quoteName('created').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'popular':
				$query[] = ' '.$db->quoteName('hits').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
			case 'random':
				$query[] = ' RAND() ';
				break;
			default :
				$query[] = ' '.$db->quoteName('title').' '.$ordering_dir.', '.$db->quoteName('id').' '.$ordering_dir;
				break;
		}

		$query = implode(' ', $query);

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

		$db->setQuery( $query, $start, $limit );

		if ($type == 'items') {
			$result	= $db->loadObjectList();
			if (!$result)
			{
				$result = array();
			}

			if ($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			$videos = array();

			foreach ($result as $row) {
				$video = ES::video($row->uid, $row->type);
				$video->load($row);

				$cluster = $video->getCluster();

				$video->creator = $video->getVideoCreator($cluster);

				$videos[] = $video;
			}

			return $videos;
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();

			return $result;
		}
	}

	private function getAccessColumn($type = 'access', $prefix = 'a')
	{
		$column = '';
		if ($type == 'access') {
			$column = "(select pri.value as `access` from `#__social_privacy_items` as pri";
			$column .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid = " . $prefix . ".id and pri.`type` = 'videos'";
			$column .= " UNION ALL ";
			$column .= " select prm.value as `access`";
			$column .= " from `#__social_privacy_map` as prm";
			$column .= "  inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
			$column .= "  left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
			$column .= " where prm.uid = " . $prefix . ".user_id and prm.utype = 'user'";
			$column .= "  and pp.type = 'videos' and pp.rule = 'view'";
			$column .= " union all ";
			$column .= " select prm.value as `access`";
			$column .= " from `#__social_privacy_map` as prm";
			$column .= "  inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
			$column .= "  inner join `#__social_profiles_maps` pmp on prm.uid = pmp.profile_id";
			$column .= " where prm.utype = 'profiles' and pmp.user_id = " . $prefix . ".user_id";
			$column .= "  and pp.type = 'videos' and pp.rule = 'view'";
			$column .= " limit 1";
			$column .= ") as access";

		} else if ($type == 'customaccess') {

			$column = "(select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access` from `#__social_privacy_items` as pri";
			$column .= " left join `#__social_privacy_customize` as prc on pri.id = prc.uid and prc.utype = 'item' where pri.uid = " . $prefix . ".id and pri.`type` = 'videos'";
			$column .= " UNION ALL ";
			$column .= " select concat(',', group_concat(prc.user_id SEPARATOR ','), ',') as `custom_access`";
			$column .= " from `#__social_privacy_map` as prm";
			$column .= "    inner join `#__social_privacy` as pp on prm.privacy_id = pp.id";
			$column .= "    left join `#__social_privacy_customize` as prc on prm.id = prc.uid and prc.utype = 'user'";
			$column .= " where prm.uid = " . $prefix . ".user_id and prm.utype = 'user'";
			$column .= "    and pp.type = 'videos' and pp.rule = 'view'";
			$column .= " limit 1";
			$column .= ") as custom_access";

		} else if ($type == 'fieldaccess') {
			$column = "(select `field_access` from `#__social_privacy_items` as pri where pri.`uid`= " . $prefix .".`id` and pri.`type` = 'videos' limit 1) as field_access";
		}

		return $column;
	}
}
