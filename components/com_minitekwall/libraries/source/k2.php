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

// K2 framework
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

class MinitekWallLibSourceK2
{
	public static function RecurseK2Categories($id, $k2_categories_array)
	{
		$mainframe = JFactory::getApplication();
		$id = (int)$id;
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$query = "SELECT ".$db->quoteName('id')." FROM ".$db->quoteName('#__k2_categories')."
		WHERE ".$db->quoteName('parent')." = {$id}
		AND ".$db->quoteName('published')." = ".$db->Quote('1')." AND ".$db->quoteName('trash')." = ".$db->Quote('0')." ";

		$query .= " AND ".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).") ";

		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

		$db->setQuery($query);

		$children = $db->loadObjectList();

		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		if ($children)
		{
			foreach ($children as $row)
			{
				if (self::hasChildren($row->id))
				{
					$k2_categories_array[] = $row->id;
					$k2_categories_rec = self::RecurseK2Categories($row->id, $k2_categories_array);
					$k2_categories_array = array_merge($k2_categories_array, $k2_categories_rec);
					$k2_categories_array = array_unique($k2_categories_array);
				}
				else
				{
					$k2_categories_array[] = $row->id;
				}
			}
		}

		return $k2_categories_array;
	}

	public static function hasChildren($id)
	{
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = (int)$id;

		$db = JFactory::getDBO();
		$query = "SELECT * FROM ".$db->quoteName('#__k2_categories')."
		WHERE ".$db->quoteName('parent')." = {$id}
		AND ".$db->quoteName('published')." = ".$db->Quote('1')." AND ".$db->quoteName('trash')." = ".$db->Quote('0')." ";
		$query .= " AND ".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		if (count($rows))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function countCategoryItems($id)
	{
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = (int)$id;
		$db = JFactory::getDBO();
		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		$query = "SELECT COUNT(*) FROM ".$db->quoteName('#__k2_items')."
		WHERE ".$db->quoteName('catid')." = {$id} AND ".$db->quoteName('published')." = ".$db->Quote('1')."
		AND ( ".$db->quoteName('publish_up')." = ".$db->Quote($nullDate)." OR ".$db->quoteName('publish_up')." <= ".$db->Quote($now)." )
		AND ( ".$db->quoteName('publish_down')." = ".$db->Quote($nullDate)." OR ".$db->quoteName('publish_down')." >= ".$db->Quote($now)." )
		AND ".$db->quoteName('trash')." = ".$db->Quote('0')." ";

		$query .= " AND ".$db->quoteName('access')." IN (".implode(',', $user->getAuthorisedViewLevels()).") ";

		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

		$db->setQuery($query);

		$total = $db->loadResult();

		return $total;
	}

	public static function countAuthorItems($userid, $categories)
	{
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$userid = (int)$userid;
		$db = JFactory::getDBO();
		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();
		$where = '';
		if ($categories != 0)
		{
			JArrayHelper::toInteger($categories);
			$where = " a.".$db->quoteName('catid')." IN(".implode(',', $categories).") AND ";
		}

		$languageCheck = '';
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$languageCheck = "AND i.".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND c.".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
		}

		$languageCheck = '';
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$languageCheck = "AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
		}

		$query = "SELECT COUNT(*) FROM ".$db->quoteName('#__k2_items')." as a
		WHERE {$where} a.".$db->quoteName('published')." = ".$db->Quote('1')."
		AND ( a.".$db->quoteName('publish_up')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_up')." <= ".$db->Quote($now)." )
		AND ( a.".$db->quoteName('publish_down')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_down')." >= ".$db->Quote($now)." )
		AND a.".$db->quoteName('trash')." = ".$db->Quote('0')." AND a.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).")
		AND a.".$db->quoteName('created_by_alias')." = '' AND a.".$db->quoteName('created_by')." = {$userid} {$languageCheck}
		AND EXISTS (SELECT * FROM ".$db->quoteName('#__k2_categories')." as c
			WHERE c.".$db->quoteName('id')." = a.".$db->quoteName('catid')."
			AND c.".$db->quoteName('published')." = ".$db->Quote('1')." AND c.".$db->quoteName('trash')." = ".$db->Quote('0')."
			AND c.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck} )";

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	public static function getK2AuthorsData($categories, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$mainframe = JFactory::getApplication();
		$componentParams = JComponentHelper::getParams('com_k2');
		$db = JFactory::getDBO();
		$where = '';

		// Exclude authors
		$exclude_authors = $data_source['k2a_exclude_authors'];
 		if (preg_match('/^[0-9,]+$/i', $exclude_authors)) {
		  $excluded = $exclude_authors;
		} else {
		  $excluded = '0';
		}

		// Get ordering
		$ordering = 'userID';
		$ordering_direction = 'DESC';
		if (array_key_exists('k2a_ordering', $data_source))
		{
			$ordering = $data_source['k2a_ordering'];
		}
		if (array_key_exists('k2a_ordering_direction', $data_source))
		{
			$ordering_direction = $data_source['k2a_ordering_direction'];
		}

		// Filter by categories
		if ($categories != 0)
		{
			JArrayHelper::toInteger($categories);
			$where = " a.".$db->quoteName('catid')." IN(".implode(',', $categories).") AND ";
		}

		$user = JFactory::getUser();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		$languageCheck = '';
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$languageCheck = "AND a.".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
		}
		$query = "SELECT DISTINCT a.".$db->quoteName('created_by').", u.".$db->quoteName('userName')."
		FROM ".$db->quoteName('#__k2_items')." as a
		LEFT JOIN ".$db->quoteName('#__k2_users')." as u
		ON a.".$db->quoteName('created_by')." = u.".$db->quoteName('userID')."
	    WHERE {$where} a.".$db->quoteName('published')." = ".$db->Quote('1')."
		AND a.".$db->quoteName('created_by')." NOT IN ({$excluded})
	    AND ( a.".$db->quoteName('publish_up')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_up')." <= ".$db->Quote($now)." )
	    AND ( a.".$db->quoteName('publish_down')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_down')." >= ".$db->Quote($now)." )
	    AND a.".$db->quoteName('trash')." = ".$db->Quote('0')."
	    AND a.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).")
	    AND a.".$db->quoteName('created_by_alias')." = ''
		{$languageCheck}
	    AND EXISTS (SELECT * FROM ".$db->quoteName('#__k2_categories')." as c
		WHERE c.".$db->quoteName('id')." = a.".$db->quoteName('catid')."
		AND c.".$db->quoteName('published')." = ".$db->Quote('1')."
		AND c.".$db->quoteName('trash')." = ".$db->Quote('0')."
		AND c.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck})
		ORDER BY u.".$db->quoteName($ordering)." ".$ordering_direction.", u.".$db->quoteName('userID')." ".$ordering_direction."";

		// Set the list start limit
		$page = $mainframe->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $mainframe->input->get('pagination');
			if ($mainframe->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
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

		// Get Data
		$db->setQuery($query, $start, $limit);

		$rows = $db->loadObjectList();

		return $rows;
	}

	public static function getK2AuthorsDataCount($categories, $data_source, $globalLimit)
	{
		$mainframe = JFactory::getApplication();
		$componentParams = JComponentHelper::getParams('com_k2');
		$db = JFactory::getDBO();
		$where = '';

		// Exclude authors
		$exclude_authors = $data_source['k2a_exclude_authors'];
 		if (preg_match('/^[0-9,]+$/i', $exclude_authors)) {
		  $excluded = $exclude_authors;
		} else {
		  $excluded = '0';
		}

		// Get ordering
		$ordering = 'userID';
		$ordering_direction = 'DESC';
		if (array_key_exists('k2a_ordering', $data_source))
		{
			$ordering = $data_source['k2a_ordering'];
		}
		if (array_key_exists('k2a_ordering_direction', $data_source))
		{
			$ordering_direction = $data_source['k2a_ordering_direction'];
		}

		// Filter by categories
		if ($categories != 0)
		{
			JArrayHelper::toInteger($categories);
			$where = " a.".$db->quoteName('catid')." IN(".implode(',', $categories).") AND ";
		}

		$user = JFactory::getUser();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		$languageCheck = '';
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$languageCheck = "AND a.".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
		}
		$query = "SELECT DISTINCT a.".$db->quoteName('created_by').", u.".$db->quoteName('userName')."
		FROM ".$db->quoteName('#__k2_items')." as a
		LEFT JOIN ".$db->quoteName('#__k2_users')." as u
		ON a.".$db->quoteName('created_by')." = u.".$db->quoteName('userID')."
	    WHERE {$where} a.".$db->quoteName('published')." = ".$db->Quote('1')."
		AND a.".$db->quoteName('created_by')." NOT IN ({$excluded})
	    AND ( a.".$db->quoteName('publish_up')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_up')." <= ".$db->Quote($now)." )
	    AND ( a.".$db->quoteName('publish_down')." = ".$db->Quote($nullDate)." OR a.".$db->quoteName('publish_down')." >= ".$db->Quote($now)." )
	    AND a.".$db->quoteName('trash')." = ".$db->Quote('0')."
	    AND a.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).")
	    AND a.".$db->quoteName('created_by_alias')." = ''
		{$languageCheck}
	    AND EXISTS (SELECT * FROM ".$db->quoteName('#__k2_categories')." as c
		WHERE c.".$db->quoteName('id')." = a.".$db->quoteName('catid')."
		AND c.".$db->quoteName('published')." = ".$db->Quote('1')."
		AND c.".$db->quoteName('trash')." = ".$db->Quote('0')."
		AND c.".$db->quoteName('access')." IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck})
		ORDER BY u.".$db->quoteName($ordering)." ".$ordering_direction.", u.".$db->quoteName('userID')." ".$ordering_direction."";

		// Set the list start limit
		$start = 0;
		$limit = $globalLimit;

		// Get Data
		$db->setQuery($query, $start, $limit);

		$db->query();
		$itemCount = $db->getNumRows();

		return $itemCount;
	}

	// Get K2 Items
	public static function getK2Items($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$mainframe = JFactory::getApplication();

		// Set the list start limit
		$page = $mainframe->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $mainframe->input->get('pagination');
			if ($mainframe->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
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

		// Params
		//$dataSource = $data_source['k2i_data_source'];
		$dataSource = 'custom_filtering';
		if (!array_key_exists('k2i_category_id', $data_source))
		{
			$cid = NULL;
		} else {
			$cid = $data_source['k2i_category_id'];
		}
		$ordering = $data_source['k2i_ordering'];
		$orderdir = $data_source['k2i_ordering_direction'];
		$category_type = $data_source['k2i_category_filtering_type'] ? 'IN' : 'NOT IN';
		$tag_type = $data_source['k2i_tag_filtering_type'];
		if (!array_key_exists('k2i_tag_id', $data_source))
		{
			$tagIds = NULL;
		} else {
			$tagIds = $data_source['k2i_tag_id'];
		}
		if ($tagIds)
			$tag_string = implode(',', $tagIds);

		// Exclude items script
		$excluded = $data_source['k2i_exclude_items'];
		if (preg_match('/^[0-9,]+$/i', $excluded)) {
		  $excluded_str = $excluded;
		} else {
		  $excluded_str = '0';
		}

		// Get variables
		$componentParams = JComponentHelper::getParams('com_k2');
		$limitstart = $mainframe->input->get('limitstart', '', 'INT');
		$user = JFactory::getUser();
		$aid = $user->get('aid');
		$db = JFactory::getDBO();
		$jnow = JFactory::getDate();
		$now =  K2_JVERSION == '15'?$jnow->toMySQL():$jnow->toSql();
		$nullDate = $db->getNullDate();

		// Data source = Specific Items
		if ($dataSource == 'specific_items')
		{
			if (!array_key_exists('k2i_items', $data_source))
			{
				$value = NULL;
			} else {
				$value = $data_source['k2i_items'];
			}
			$current = array();
			if (is_string($value) && !empty($value))
				$current[] = $value;
			if (is_array($value))
				$current = $value;

			$items = array();

			foreach ($current as $id)
			{
				$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams

				FROM #__k2_items as i
				LEFT JOIN #__k2_categories c ON c.id = i.catid
				WHERE i.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND i.access<={$aid} ";
				}
				$query .= " AND i.trash = 0 AND c.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND c.access<={$aid} ";
				}
				$query .= " AND c.trash = 0
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )
				AND i.id={$id}";
				if (K2_JVERSION != '15')
				{
					if ($mainframe->getLanguageFilter())
					{
						$languageTag = JFactory::getLanguage()->getTag();
						$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
					}
				}

				$db->setQuery($query);
				$item = $db->loadObject();
				if ($item)
					$items[] = $item;
			}
		}

		// Data source = Custom filtering
		else if ($dataSource == 'custom_filtering')
		{
			$query = "SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

			if ($ordering == 'best')
				$query .= ", (r.rating_sum/r.rating_count) AS rating";

			if ($ordering == 'comments')
				$query .= ", COUNT(comments.id) AS numOfComments";

			$query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

			if ($ordering == 'best')
				$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

			if ($ordering == 'comments')
				$query .= " LEFT JOIN #__k2_comments comments ON comments.itemID = i.id";

			if ($tagIds)
			{
				$query .= " LEFT JOIN #__k2_tags_xref tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags tags ON tags.id = tags_xref.tagID ";
			}

			if (K2_JVERSION != '15')
			{
				$query .= " WHERE i.published = 1 AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")  AND c.trash = 0";
			}
			else
			{
				$query .= " WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
			}

			$query .= " AND i.id NOT IN ({$excluded_str}) ";

			// Time range
			$query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
			$query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
			if ($data_source['k2i_popularity_range'])
			{
				$datenow = JFactory::getDate();
				$date =  K2_JVERSION == '15'?$datenow->toMySQL():$datenow->toSql();
				$query .= " AND i.created > DATE_SUB('{$date}',INTERVAL ".$data_source['k2i_popularity_range']." DAY) ";
			}

			if (!is_null($cid))
			{
				if (is_array($cid))
				{
					if ($data_source['k2i_get_children'])
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$sql = @implode(',', $categories);
						$query .= " AND i.catid ".$category_type." ({$sql})";

					}
					else
					{
						JArrayHelper::toInteger($cid);
						$query .= " AND i.catid ".$category_type." (".implode(',', $cid).")";
					}
				}
				else
				{
					if ($data_source['k2i_get_children'])
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$sql = @implode(',', $categories);
						$query .= " AND i.catid ".$category_type." ({$sql})";
					}
					else
					{
						$query .= " AND i.catid ".$category_type." (".(int)$cid.")";
					}
				}
			}

			if ($data_source['k2i_featured_items'] == '0')
				$query .= " AND i.featured != 1";

			if ($data_source['k2i_featured_items'] == '2')
				$query .= " AND i.featured = 1";

			if ($data_source['k2i_videos_only'])
				$query .= " AND (i.video IS NOT NULL AND i.video!='')";

			if ($ordering == 'comments')
				$query .= " AND comments.published = 1";

			if (K2_JVERSION != '15')
			{
				if ($mainframe->getLanguageFilter())
				{
					$languageTag = JFactory::getLanguage()->getTag();
					$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
				}
			}

			if ($tagIds)
			{
				if ($tag_type)
				{
					$query .= " AND tags_xref.tagID IN (".$tag_string.")";
				} else {
					$query .= " AND (tags_xref.tagID IS NULL OR tags_xref.tagID NOT IN (".$tag_string."))";
				}
			}

			switch ($ordering)
			{
				case 'date' :
					$orderby = 'i.created '.$orderdir.', i.id '.$orderdir;
					break;

				case 'publishUp' :
					$orderby = 'i.publish_up '.$orderdir.', i.id '.$orderdir;
					break;

				case 'alpha' :
					$orderby = 'i.title '.$orderdir.', i.id '.$orderdir;
					break;

				case 'order' :
					if ($data_source['k2i_featured_items'] == '2')
						$orderby = 'i.featured_ordering '.$orderdir.', i.created '.$orderdir;
					else
						$orderby = 'i.ordering '.$orderdir.', i.id '.$orderdir;
					break;

				case 'hits' :
					$orderby = 'i.hits '.$orderdir.', i.id '.$orderdir;
					break;

				case 'best' :
					$orderby = 'rating '.$orderdir.', i.id '.$orderdir;
					break;

				case 'comments' :
					$orderby = 'numOfComments '.$orderdir.', i.id '.$orderdir;
					break;

				case 'modified' :
					$orderby = 'lastChanged '.$orderdir.', i.id '.$orderdir;
					break;

				case 'rand' :
					$orderby = 'RAND()';
					break;

				default :
					$orderby = 'i.id '.$orderdir;
					break;
			}

			$query .= " GROUP BY i.id ";
			$query .= " ORDER BY ".$orderby;

			$db->setQuery($query, $start, $limit);
			$items = $db->loadObjectList();
		}

		return $items;
	}

	// Get K2 Items Count
	public static function getK2ItemsCount($data_source, $globalLimit)
	{
		$mainframe = JFactory::getApplication();

		// Set the list start limit
		$start = 0;
		$limit = $globalLimit;

		// Params
		//$dataSource = $data_source['k2i_data_source'];
		$dataSource = 'custom_filtering';
		if (!array_key_exists('k2i_category_id', $data_source))
		{
			$cid = NULL;
		} else {
			$cid = $data_source['k2i_category_id'];
		}
		$ordering = $data_source['k2i_ordering'];
		$orderdir = $data_source['k2i_ordering_direction'];
		$category_type = $data_source['k2i_category_filtering_type'] ? 'IN' : 'NOT IN';
		$tag_type = $data_source['k2i_tag_filtering_type'];
		if (!array_key_exists('k2i_tag_id', $data_source))
		{
			$tagIds = NULL;
		} else {
			$tagIds = $data_source['k2i_tag_id'];
		}
		if ($tagIds)
			$tag_string = implode(',', $tagIds);

		// Exclude items script
		$excluded = $data_source['k2i_exclude_items'];
		if (preg_match('/^[0-9,]+$/i', $excluded)) {
		  $excluded_str = $excluded;
		} else {
		  $excluded_str = '0';
		}

		// Get variables
		$componentParams = JComponentHelper::getParams('com_k2');
		$user = JFactory::getUser();
		$aid = $user->get('aid');
		$db = JFactory::getDBO();
		$jnow = JFactory::getDate();
		$now =  K2_JVERSION == '15'?$jnow->toMySQL():$jnow->toSql();
		$nullDate = $db->getNullDate();

		// Data source = Specific Items
		if ($dataSource == 'specific_items')
		{
			if (!array_key_exists('k2i_items', $data_source))
			{
				$value = NULL;
			} else {
				$value = $data_source['k2i_items'];
			}
			$current = array();
			if (is_string($value) && !empty($value))
				$current[] = $value;
			if (is_array($value))
				$current = $value;

			$items = array();

			foreach ($current as $id)
			{

				$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams

				FROM #__k2_items as i
				LEFT JOIN #__k2_categories c ON c.id = i.catid
				WHERE i.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND i.access<={$aid} ";
				}
				$query .= " AND i.trash = 0 AND c.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND c.access<={$aid} ";
				}
				$query .= " AND c.trash = 0
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )
				AND i.id={$id}";
				if (K2_JVERSION != '15')
				{
					if ($mainframe->getLanguageFilter())
					{
						$languageTag = JFactory::getLanguage()->getTag();
						$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
					}
				}

				$db->setQuery($query);
				$item = $db->loadObject();
				if ($item)
					$items[] = $item;

			}
			// Slice array - limit
			$items = array_slice($items, $start, $limit);

			$itemCount = count($items);
		}

		// Data source = Custom filtering
		else if ($dataSource == 'custom_filtering')
		{
			$query = "SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

			if ($ordering == 'best')
				$query .= ", (r.rating_sum/r.rating_count) AS rating";

			if ($ordering == 'comments')
				$query .= ", COUNT(comments.id) AS numOfComments";

			$query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

			if ($ordering == 'best')
				$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

			if ($ordering == 'comments')
				$query .= " LEFT JOIN #__k2_comments comments ON comments.itemID = i.id";

			if ($tagIds)
			{
				$query .= " LEFT JOIN #__k2_tags_xref tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags tags ON tags.id = tags_xref.tagID ";
			}

			if (K2_JVERSION != '15')
			{
				$query .= " WHERE i.published = 1 AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")  AND c.trash = 0";
			}
			else
			{
				$query .= " WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
			}

			$query .= " AND i.id NOT IN ({$excluded_str}) ";

			// Time range
			$query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
			$query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
			if ($data_source['k2i_popularity_range'])
			{
				$datenow = JFactory::getDate();
				$date =  K2_JVERSION == '15'?$datenow->toMySQL():$datenow->toSql();
				$query .= " AND i.created > DATE_SUB('{$date}',INTERVAL ".$data_source['k2i_popularity_range']." DAY) ";
			}

			if (!is_null($cid))
			{
				if (is_array($cid))
				{
					if ($data_source['k2i_get_children'])
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$sql = @implode(',', $categories);
						$query .= " AND i.catid ".$category_type." ({$sql})";

					}
					else
					{
						JArrayHelper::toInteger($cid);
						$query .= " AND i.catid ".$category_type." (".implode(',', $cid).")";
					}
				}
				else
				{
					if ($data_source['k2i_get_children'])
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$sql = @implode(',', $categories);
						$query .= " AND i.catid ".$category_type." ({$sql})";
					}
					else
					{
						$query .= " AND i.catid ".$category_type." (".(int)$cid.")";
					}

				}
			}

			if ($data_source['k2i_featured_items'] == '0')
				$query .= " AND i.featured != 1";

			if ($data_source['k2i_featured_items'] == '2')
				$query .= " AND i.featured = 1";

			if ($data_source['k2i_videos_only'])
				$query .= " AND (i.video IS NOT NULL AND i.video!='')";

			if ($ordering == 'comments')
				$query .= " AND comments.published = 1";

			if (K2_JVERSION != '15')
			{
				if ($mainframe->getLanguageFilter())
				{
					$languageTag = JFactory::getLanguage()->getTag();
					$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
				}
			}

			if ($tagIds)
			{
				if ($tag_type)
				{
					$query .= " AND tags_xref.tagID IN (".$tag_string.")";
				} else {
					$query .= " AND (tags_xref.tagID IS NULL OR tags_xref.tagID NOT IN (".$tag_string."))";
				}
			}

			switch ($ordering)
			{
				case 'date' :
					$orderby = 'i.created '.$orderdir.', i.id '.$orderdir;
					break;

				case 'publishUp' :
					$orderby = 'i.publish_up '.$orderdir.', i.id '.$orderdir;
					break;

				case 'alpha' :
					$orderby = 'i.title '.$orderdir.', i.id '.$orderdir;
					break;

				case 'order' :
					if ($data_source['k2i_featured_items'] == '2')
						$orderby = 'i.featured_ordering '.$orderdir.', i.id '.$orderdir;
					else
						$orderby = 'i.ordering '.$orderdir.', i.id '.$orderdir;
					break;

				case 'hits' :
					$orderby = 'i.hits '.$orderdir.', i.id '.$orderdir;
					break;

				case 'best' :
					$orderby = 'rating '.$orderdir.', i.id '.$orderdir;
					break;

				case 'comments' :
					$orderby = 'numOfComments '.$orderdir.', i.id '.$orderdir;
					break;

				case 'modified' :
					$orderby = 'lastChanged '.$orderdir.', i.id '.$orderdir;
					break;

				case 'rand' :
					$orderby = 'RAND()';
					break;

				default :
					$orderby = 'i.id '.$orderdir;
					break;
			}

			$query .= " GROUP BY i.id ";
			$query .= " ORDER BY ".$orderby;

			$db->setQuery($query, $start, $limit);

			$db->query();
			$itemCount = $db->getNumRows();
		}

		return $itemCount;
	}

	// Get K2 Categories
	public static function getK2Categories($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		if (!array_key_exists('k2c_category_id', $data_source))
		{
			$k2_categories = NULL;
			return false;
		}
		else
		{
			$k2_categories = $data_source['k2c_category_id'];
		}

		$k2_include_children = $data_source['k2c_include_children'];
		$ordering = $data_source['k2c_ordering'];
		$orderdir = $data_source['k2c_ordering_direction'];

		$k2_categories_array = array();

		foreach ($k2_categories as $k2_category)
		{
			$k2_categories_array[] = $k2_category;

			if ($k2_include_children)
			{
				// Recursive function
				$k2_categories_recurse = MinitekWallLibSourceK2::RecurseK2Categories($k2_category, $k2_categories_array);
				$k2_categories_array = array_merge($k2_categories_array, $k2_categories_recurse);
			}
		}

		$k2_categories_array = array_unique($k2_categories_array);
		$categories_str = implode(',', $k2_categories_array);

		// Query
		$query = "SELECT * FROM ".$db->quoteName('#__k2_categories')." ";
		$query .= "WHERE ".$db->quoteName('id')." IN ({$categories_str}) ";
		$query .= "AND ".$db->quoteName('published')." = ".$db->Quote('1')." AND ".$db->quoteName('trash')." = ".$db->Quote('0')." ";
		$query .= " AND ".$db->quoteName('access')." IN (".implode(',', $user->getAuthorisedViewLevels()).") ";

		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

		// Ordering
		switch ($ordering)
		{
			case 'alpha' :
				$orderby = 'name '.$orderdir.', id '.$orderdir;
				break;

			case 'order' :
				$orderby = 'ordering '.$orderdir.', id '.$orderdir;
				break;

			case 'rand' :
				$orderby = 'RAND()';
				break;

			default :
				$orderby = 'name '.$orderdir.', id '.$orderdir;
				break;
		}
		$query .= " ORDER BY ".$orderby;

		// Set the list start limit
		$page = $mainframe->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);

			$pagination = $mainframe->input->get('pagination');
			if ($mainframe->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
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

		// Get Data
		$db->setQuery($query, $start, $limit);

		$items = $db->loadObjectList();

		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		return $items;
	}

	// Get K2 Categories Count
	public static function getK2CategoriesCount($data_source, $globalLimit)
	{
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		if (!array_key_exists('k2c_category_id', $data_source))
		{
			$k2_categories = NULL;
			return false;
		}
		else
		{
			$k2_categories = $data_source['k2c_category_id'];
		}

		$k2_include_children = $data_source['k2c_include_children'];
		$ordering = $data_source['k2c_ordering'];
		$orderdir = $data_source['k2c_ordering_direction'];

		$k2_categories_array = array();

		foreach ($k2_categories as $k2_category)
		{
			$k2_categories_array[] = $k2_category;

			if ($k2_include_children)
			{
				// Recursive function
				$k2_categories_recurse = MinitekWallLibSourceK2::RecurseK2Categories($k2_category, $k2_categories_array);
				$k2_categories_array = array_merge($k2_categories_array, $k2_categories_recurse);
			}
		}

		$k2_categories_array = array_unique($k2_categories_array);
		$categories_str = implode(',', $k2_categories_array);

		// Query
		$query = "SELECT * FROM ".$db->quoteName('#__k2_categories')." ";
		$query .= "WHERE ".$db->quoteName('id')." IN ({$categories_str}) ";
		$query .= "AND ".$db->quoteName('published')." = ".$db->Quote('1')." AND ".$db->quoteName('trash')." = ".$db->Quote('0')." ";
		$query .= " AND ".$db->quoteName('access')." IN (".implode(',', $user->getAuthorisedViewLevels()).") ";

		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND ".$db->quoteName('language')." IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

		// Ordering
		switch ($ordering)
		{
			case 'alpha' :
				$orderby = 'name '.$orderdir.', id '.$orderdir;
				break;

			case 'order' :
				$orderby = 'ordering '.$orderdir.', id '.$orderdir;
				break;

			case 'rand' :
				$orderby = 'RAND()';
				break;

			default :
				$orderby = 'name '.$orderdir.', id '.$orderdir;
				break;
		}
		$query .= " ORDER BY ".$orderby;

		// Set the list start limit
		$start = 0;
		$limit = $globalLimit;

		// Get Data
		$db->setQuery($query, $start, $limit);
		$db->query();
		$itemCount = $db->getNumRows();

		return $itemCount;
	}

	// Get K2 Authors
	public static function getK2Authors($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		if (!array_key_exists('k2a_category_id', $data_source))
		{
			$k2_authors_categories = NULL;
			return false;
		}
		else
		{
			$k2_authors_categories = $data_source['k2a_category_id'];
		}
		$k2a_include_children = $data_source['k2a_include_children'];

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

		// Specific categories
		$k2_authors_categories_array = array();

		foreach ($k2_authors_categories as $k2_authors_category)
		{
			$k2_authors_categories_array[] = $k2_authors_category;

			if ($k2a_include_children)
			{
				// Recursive function
				$k2_authors_categories_recurse = MinitekWallLibSourceK2::RecurseK2Categories($k2_authors_category, $k2_authors_categories_array);
				$k2_authors_categories_array = array_merge($k2_authors_categories_array, $k2_authors_categories_recurse);
			}
		}

		$k2_authors_categories_array = array_unique($k2_authors_categories_array);

		$k2_authors_categories_object = MinitekWallLibSourceK2::getK2AuthorsData($k2_authors_categories_array, $data_source, $startLimit, $pageLimit, $globalLimit);

		return $k2_authors_categories_object;
	}

	// Get K2 Authors Count
	public static function getK2AuthorsCount($data_source, $globalLimit)
	{
		if (!array_key_exists('k2a_category_id', $data_source))
		{
			$k2_authors_categories = NULL;
			return false;
		}
		else
		{
			$k2_authors_categories = $data_source['k2a_category_id'];
		}
		$k2a_include_children = $data_source['k2a_include_children'];

		// Specific categories
		$k2_authors_categories_array = array();

		foreach ($k2_authors_categories as $k2_authors_category)
		{
			$k2_authors_categories_array[] = $k2_authors_category;

			if ($k2a_include_children)
			{
				// Recursive function
				$k2_authors_categories_recurse = MinitekWallLibSourceK2::RecurseK2Categories($k2_authors_category, $k2_authors_categories_array);
				$k2_authors_categories_array = array_merge($k2_authors_categories_array, $k2_authors_categories_recurse);
			}
		}

		$k2_authors_categories_array = array_unique($k2_authors_categories_array);
		$k2_authors_categories_object_count = MinitekWallLibSourceK2::getK2AuthorsDataCount($k2_authors_categories_array, $data_source, $globalLimit);

		return $k2_authors_categories_object_count;
	}
}
