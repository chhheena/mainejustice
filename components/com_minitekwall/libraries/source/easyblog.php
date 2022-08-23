<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

$path = JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';

jimport('joomla.filesystem.file');

if (!JFile::exists($path)) {
	return;
}

// @task: Include main helper file.
require_once($path);

// @task: Load component's language file.
JFactory::getLanguage()->load('com_easyblog', JPATH_ROOT);

class MinitekWallLibSourceEasyblog
{
	// Get Easyblog Articles
	public static function getEasyblogArticles($type, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit = $startLimit;
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

		$db = EB::db();
		$my	= JFactory::getUser();
		$config	= EB::config();

		// Categories
		$category_filtering_type = $data_source['eb_category_filtering_type'] ? 'IN' : 'NOT IN';
		$categories_get_children = $data_source['eb_getChildren'];

		if (array_key_exists('eb_catids', $data_source))
		{
			$categories	= EB::getCategoryInclusion( $data_source['eb_catids'] );
		}
		else
		{
			$categories	= false;
		}
		$catIds = array();
		if( !empty( $categories ) )
		{
			if( !is_array( $categories ) )
			{
				$categories	= array($categories);
			}
			foreach($categories as $item)
			{
				$category = new stdClass();
				$category->id = trim( $item );

				$catIds[] = $category->id;

				if( $categories_get_children )
				{
					$category->childs = null;
					EB::buildNestedCategories($category->id, $category , false , true );
					EB::accessNestedCategoriesId($category, $catIds);
				}
			}
			$catIds = array_unique( $catIds );
		}
		$cid = $catIds; // array

		if ($data_source['eb_category_filtering_type'])
		{
			JArrayHelper::toInteger($cid);
			$categoryIds = implode(',', $cid); // comma separated string
		} else {
			$categoryIds = array_merge( $cid, EB::getPrivateCategories() );
			$categoryIds = implode(',', $categoryIds); // comma separated string
		}

		// Authors
		$author_filtering_type = $data_source['eb_bloggerlisttype'] ? 'IN' : 'NOT IN';
		$bloggerIds_temp = $data_source['eb_bloggerlist']; // comma separated string
		if (preg_match('/^[0-9,]+$/i', $bloggerIds_temp)) {
		  $bloggerIds = $bloggerIds_temp;
		} else {
		  $bloggerIds = false;
		}

		// Tags
		$tag_filtering_type = $data_source['eb_tag_filtering_type'] ? 'IN' : 'NOT IN';
		$tagIds_temp = $data_source['eb_tagids']; // comma separated string
		if (preg_match('/^[0-9,]+$/i', $tagIds_temp)) {
		  $tagIds = $tagIds_temp;
		  $tagIds = trim($tagIds, ',');
		} else {
		  $tagIds = false;
		}

		// Teams
		$team_filtering_type = $data_source['eb_team_filtering_type'] ? 'IN' : 'NOT IN';
		$teamBlogIds_temp = $data_source['eb_teamids']; // comma separated string
		if (preg_match('/^[0-9,]+$/i', $teamBlogIds_temp)) {
		  $teamBlogIds = $teamBlogIds_temp;
		  $teamBlogIds = trim($teamBlogIds, ',');
		} else {
		  $teamBlogIds = false;
		}

		// Exclude items
		$excludeBlogs_temp = $data_source['eb_exclude_items']; // comma separated string
		if (preg_match('/^[0-9,]+$/i', $excludeBlogs_temp)) {
		  $excludeBlogs = $excludeBlogs_temp;
		  $excludeBlogs = trim($excludeBlogs, ',');
		} else {
		  $excludeBlogs = false;
		}

		// Query variables
		$query = '';
		$queryWhere	= '';
		$queryOrder	= '';

		// Other variables
		$sort = $data_source['eb_sortby'];
		$sortDirection = $data_source['eb_sortDirection'];
		$frontpage = (int) $data_source['eb_showFrontpage'];
		$protected = true;
		$limitType = 'listlength';
		$usefeatured = (int) $data_source['eb_usefeatured'];

		//////////////
		// Start query
		//////////////

		// Show only published
		$queryWhere	.= ' WHERE (a.`published` = 1) ';
		$queryWhere	.= ' AND a.`state` = 0';

		// Categories
		if (!empty($categoryIds))
		{
			$queryWhere	.= ' AND c.`category_id` '.$category_filtering_type.' ('.$categoryIds.')';
		}

		// Authors
		if (!empty($bloggerIds))
		{
			$queryWhere .= ' AND a.`created_by` '.$author_filtering_type.' ('.$bloggerIds.')';
		}

		// Tags
		if (!empty($tagIds))
		{
			$queryWhere  .= ' AND t.`tag_id` '.$tag_filtering_type.' ('.$tagIds.')';
		}

		// Team blogs
		if (!empty($teamBlogIds))
		{
			$queryWhere .= ' AND u.`team_id` '.$team_filtering_type.' ('.$teamBlogIds.')';
		}

		// Exclude Posts
		if(! empty($excludeBlogs))
		{
			$queryWhere .= ' AND a.`id` NOT IN ('.$excludeBlogs.') ';
		}

		// Show Frontpage posts
		if( !$frontpage )
		{
			$queryWhere	.= ' AND a.`frontpage` = ' . $db->Quote('0');
		} else if( $frontpage == 2 )
		{
			$queryWhere	.= ' AND a.`frontpage` = ' . $db->Quote('1');
		}

		// Filter by language
		if (EB::getJoomlaVersion() >= '1.6') {
			// @rule: When language filter is enabled, we need to detect the appropriate contents
			$filterLanguage = JFactory::getApplication()->getLanguageFilter();

			if( $filterLanguage )
			{
				$queryWhere	.= ' AND (';
				$queryWhere	.= ' a.`language`=' . $db->Quote( JFactory::getLanguage()->getTag() );
				$queryWhere	.= ' OR a.`language`=' . $db->Quote( '' );
				$queryWhere	.= ' OR a.`language`=' . $db->Quote( '*' );
				$queryWhere	.= ' )';
			}
		}

		// Do not list out protected blog in rss
		if ($app->input->get('format', '') == 'feed')
		{
			if($config->get('main_password_protect', true))
			{
				$queryWhere	.= ' AND a.`blogpassword`="" ';
			}
		}

		// Hide protected posts
		if($protected == false)
		{
			$queryWhere	.= ' AND a.`blogpassword` = ""';
		}

		// Blog privacy setting
		if( $my->id == 0)
		{
			$queryWhere .= ' AND a.`access` = ' . $db->Quote(BLOG_PRIVACY_PUBLIC);
		}

		// Get Posts table
		$query .= 'SELECT a.`id` AS key1, a.*, b.`id` as key2, b.`title` as `category`';
		if (!empty($teamBlogIds))
			$query  .= ' ,u.`team_id` ';
		$query .= ' FROM `#__easyblog_post` AS a';

		// Join Categories
		$query .= ' LEFT JOIN `#__easyblog_category` AS b';
		$query .= ' ON a.category_id = b.id';

		// Join Featured
		if( $usefeatured )
		{
			$query	.= ' INNER JOIN `#__easyblog_featured` AS f';
			$query	.= ' 	ON a.`id` = f.`content_id` AND f.`type` = ' . $db->Quote('post');
		}

		// Join Categories
		if (!empty($categoryIds))
		{
			$query  .= ' LEFT JOIN `#__easyblog_post_category` AS c ON a.`id` = c.`post_id`';
		}

		// Join Tags
		if (!empty($tagIds))
		{
			$query  .= ' LEFT JOIN `#__easyblog_post_tag` AS t ON a.`id` = t.`post_id`';
		}

		// Join Teams
		if (!empty($teamBlogIds))
		{
			$query  .= ' LEFT JOIN `#__easyblog_team_post` AS u ON a.id = u.post_id';
		}

		// Get ordering
		$queryOrder .= ' ORDER BY ';
		switch( $sort )
		{
			case 'latest':
				$queryOrder	.= ' a.`created` '.$sortDirection.', a.`id` '.$sortDirection;
				break;
			case 'published':
				$queryOrder	.= ' a.`publish_up` '.$sortDirection.', a.`id` '.$sortDirection;
				break;
			case 'modified':
				$queryOrder	.= ' a.`modified` '.$sortDirection.', a.`id` '.$sortDirection;
				break;
			case 'popular':
				$queryOrder	.= ' a.`hits` '.$sortDirection.', a.`id` '.$sortDirection;
				break;
			case 'alphabet':
				$queryOrder	.= ' a.`title` '.$sortDirection.', a.`id` '.$sortDirection;
				break;
			case 'random':
				$queryOrder	.= ' RAND() ';
				break;
			default :
				break;
		}

		// Create query
		$query	.= $queryWhere;
		$query  .= ' GROUP BY a.id ';
		$query	.= $queryOrder;
		$query	.= ' LIMIT '.$start.', '.$limit.'';

		// Execute query
		$db->setQuery($query);

		if($db->getErrorNum() > 0)
		{
			JError::raiseError( $db->getErrorNum() , $db->getErrorMsg() . $db->stderr());
		}

		if ($type == 'items') {
			$items = $db->loadObjectList();

			return $items;
		return $items;
		} else if ($type == 'count') {
			$db->query();
			$result = $db->getNumRows();

			return $result;
		}
	}
}
