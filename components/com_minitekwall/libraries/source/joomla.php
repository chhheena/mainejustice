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

$com_path = JPATH_SITE.'/components/com_content/';
if (!class_exists('ContentRouter'))
{
	require_once $com_path.'router.php';
	require_once $com_path.'helpers/route.php';
}
JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');

class MinitekWallLibSourceJoomla
{
	// Get Joomla Articles
	public static function getJoomlaArticles($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$articles_limit = $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$articles_limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $articles_limit);

			$view = $app->input->get('view', '');
			$pagination = $app->input->get('pagination', '');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
				$start = 0;
				$articles_limit = $start_limit + (($page - 1) * $articles_limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$articles_limit = $globalLimit - $start;
				}
			} else {
				$articles_limit = 0;
			}
		}

		// Set the filters based on the widget params
		$articles->setState('list.start', $start);
		$articles->setState('list.limit', $articles_limit);
		$articles->setState('filter.published', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$articles->setState('filter.access', $access);

		// Prep for Normal or Dynamic Modes
		$mode = $data_source['ja_mode'];
		switch ($mode)
		{
			case 'dynamic':
				$option = $app->input->get('option');
				$view = $app->input->get('view');
				if ($option === 'com_content')
				{
					switch($view)
					{
						case 'category':
							$catids = array($app->input->getInt('id'));
							break;
						case 'categories':
							$catids = array($app->input->getInt('id'));
							break;
						case 'article':
							if ($params->get('show_on_article_page', 1))
							{
								$article_id = $app->input->getInt('id');
								$catid = $app->input->getInt('catid');

								if (!$catid)
								{
									// Get an instance of the generic article model
									$article = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));

									$article->setState('params', $appParams);
									$article->setState('filter.published', 1);
									$article->setState('article.id', (int) $article_id);
									$item = $article->getItem();

									$catids = array($item->catid);
								}
								else
								{
									$catids = array($catid);
								}
							}
							else {
								// Return right away if show_on_article_page option is off
								return;
							}
							break;

						case 'featured':
						default:
							// Return right away if not on the category or article views
							return;
					}
				}
				else {
					// Return right away if not on a com_content page
					return;
				}

				break;

			case 'normal':
			default:
				if (array_key_exists('ja_catid', $data_source))
				{
					$catids = $data_source['ja_catid'];
					$articles->setState('filter.category_id.include', (bool) $data_source['ja_category_filtering_type']);
				} else {
					$catids = '';
				}

				if (array_key_exists('ja_tag_id', $data_source))
				{
					$tagIds = $data_source['ja_tag_id'];
				} else {
					$tagIds = '';
				}
				break;
		}

		// Category filter
		if ($catids)
		{
			if ($data_source['ja_show_child_category_articles'] && (int) $data_source['ja_levels'] > 0)
			{
				// Get an instance of the generic categories model
				$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$categories->setState('params', $appParams);
				$levels = $data_source['ja_levels'] ? $data_source['ja_levels'] : 9999;
				$categories->setState('filter.get_children', $levels);
				$categories->setState('filter.published', 1);
				$categories->setState('filter.access', $access);
				$additional_catids = array();

				foreach ($catids as $catid)
				{
					$categories->setState('filter.parentId', $catid);
					$recursive = true;
					$items = $categories->getItems($recursive);

					if ($items)
					{
						foreach ($items as $category)
						{
							$condition = (($category->level - $categories->getParent()->level) <= $levels);
							if ($condition)
							{
								$additional_catids[] = $category->id;
							}

						}
					}
				}

				$catids = array_unique(array_merge($catids, $additional_catids));
			}

			$articles->setState('filter.category_id', $catids);
		}

		// Tag filter
		if ($tagIds)
		{
			$includeTagChildren = $data_source['ja_tag_include_children'];

			if ($includeTagChildren)
			{
				$tagTreeArray = array();

				foreach ($tagIds as $tag)
				{
					$th = new JHelperTags;
					$tagTreeArray = $th->getTagTreeArray($tag);
				}

				$tagIds = array_unique(array_merge($tagIds, $tagTreeArray));
			}

			$tagIds = implode(',', $tagIds);

			$articles->setState('filter.tag_id', $tagIds);
			$articles->setState('filter.tag_id.include', (bool) $data_source['ja_tag_filtering_type']);
		}

		// Ordering
		$articles->setState('list.ordering', $data_source['ja_article_ordering']);
		$articles->setState('list.direction', $data_source['ja_article_ordering_direction']);

		// New Parameters
		$articles->setState('filter.featured', $data_source['ja_show_front']);
		if (array_key_exists('ja_created_by', $data_source))
		{
			$articles->setState('filter.author_id', $data_source['ja_created_by']);
		}
		if (isset($data_source['ja_author_filtering_type']))
		{
			$articles->setState('filter.author_id.include', $data_source['ja_author_filtering_type']);
		}
		/*if (array_key_exists('ja_created_by_alias', $data_source))
		{
			$articles->setState('filter.author_alias', $data_source['ja_created_by_alias']);
		}
		$articles->setState('filter.author_alias.include', $data_source['ja_author_alias_filtering_type']);*/
		$excluded_articles = false;
		if (array_key_exists('ja_excluded_articles', $data_source))
		{
			$excluded_articles = $data_source['ja_excluded_articles'];
		}

		if ($excluded_articles)
		{
			$excluded_articles = explode("\r\n", $excluded_articles);
			$articles->setState('filter.article_id', $excluded_articles);
			$articles->setState('filter.article_id.include', false); // Exclude
		}

		$date_filtering = $data_source['ja_date_filtering'];
		if ($date_filtering !== 'off')
		{
			$articles->setState('filter.date_filtering', $date_filtering);
			$articles->setState('filter.date_field', $data_source['ja_date_field']);
			$articles->setState('filter.start_date_range', $data_source['ja_start_date_range']);
			$articles->setState('filter.end_date_range', $data_source['ja_end_date_range']);
			$articles->setState('filter.relative_date', $data_source['ja_relative_date']);
		}

		// Filter by language
		$articles->setState('filter.language', $app->getLanguageFilter());

		// Get the current user for authorisation checks
		$user	= JFactory::getUser();

		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$articles->getState(
				'list.select',
				'a.id, a.title, a.alias, a.introtext, a.fulltext, ' .
					'a.checked_out, a.checked_out_time, a.ordering, fp.ordering as fordering, ' .
					'a.catid, a.created, a.created_by, a.created_by_alias, ' .
					// Use created if modified is 0
					'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
					'a.modified_by, uam.name as modified_by_name,' .
					// Use created if publish_up is 0
					'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
					'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
					'a.hits, a.xreference, a.featured,' . ' ' . $query->length('a.fulltext') . ' AS readmore'
			)
		);

		// Process an Archived Article layout
		if ($articles->getState('filter.published') == 2)
		{
			// If badcats is not null, this means that the article is inside an archived category
			// In this case, the state is set to 2 to indicate Archived (even if the article state is Published)
			$query->select($articles->getState('list.select', 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
		}
		else
		{
			/*
			Process non-archived layout
			If badcats is not null, this means that the article is inside an unpublished category
			In this case, the state is set to 0 to indicate Unpublished (even if the article state is Published)
			*/
			$query->select($articles->getState('list.select', 'CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
		}

		$query->from('#__content AS a');

		// Join over the frontpage articles.
		$query->join('LEFT', '#__content_frontpage AS fp ON fp.content_id = a.id');

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
			->select("ua.email AS author_email")

			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Join over the categories to get parent category titles
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
			->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		// Join on voting table
		$query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count')
			->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

		// Join to check for category published state in parent categories up the tree
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');

		if ($articles->getState('filter.published') == 2)
		{
			// Find any up-path categories that are archived

			// If any up-path categories are archived, include all children in archived layout
			$subquery .= ' AND parent.published = 2 GROUP BY cat.id ';

			// Set effective state to archived if up-path category is archived
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
		}
		else
		{
			// Find any up-path categories that are not published
			// If all categories are published, badcats.id will be null, and we just use the article state
			$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';

			// Select state to unpublished if up-path category is unpublished
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
		}

		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

		// Filter by access level.
		if ($access = $articles->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')
				->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $articles->getState('filter.published');

		if (is_numeric($published))
		{
			// Use article state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where($publishedWhere . ' = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);

			// Use article state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where($publishedWhere . ' IN (' . $published . ')');
		}

		// Filter by featured state
		$featured = $articles->getState('filter.featured');

		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;

			case 'only':
				$query->where('a.featured = 1');
				break;

			case 'show':
			default:
				// Normally we do not discriminate
				// between featured/unfeatured items.
				break;
		}

		// Filter by a single or group of articles.
		$articleId = $articles->getState('filter.article_id');

		if (is_numeric($articleId))
		{
			$type = $articles->getState('filter.article_id.include', true) ? '= ' : '<> ';
			$query->where('a.id ' . $type . (int) $articleId);
		}
		elseif (is_array($articleId))
		{
			JArrayHelper::toInteger($articleId);
			$articleId = implode(',', $articleId);
			$type = $articles->getState('filter.article_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id ' . $type . ' (' . $articleId . ')');
		}

		// Filter by tags
		if ($tagIds)
		{
			$tagtype = $articles->getState('filter.tag_id.include', true) ? 'IN' : 'NOT IN';
			$tagMatch = $data_source['ja_tag_match'];
			$includeChildren = $data_source['ja_tag_include_children'];
			$tagIdsCount = count(explode(',', $tagIds));

			$query->join('INNER', '#__contentitem_tag_map AS m ON m.content_item_id = a.id');
			$query->where('m.tag_id ' . $tagtype . ' (' . $tagIds . ')');

			if ($data_source['ja_tag_filtering_type'] && !$includeChildren && !$tagMatch)
			{
				$query->group('a.id HAVING COUNT(a.id) = '.$tagIdsCount);
			}
			else if ($data_source['ja_tag_filtering_type'] && $includeChildren)
			{
				$query->group('a.id');
			}
			else if (!$data_source['ja_tag_filtering_type'])
			{
				$query->group('a.id');
			}
		}

		// Filter by a single or group of categories
		$categoryId = $articles->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid ' . $type . (int) $categoryId;

			if ($includeSubcategories)
			{
				$levels = (int) $articles->getState('filter.max_category_levels', '1');

				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true)
					->select('sub.id')
					->from('#__categories as sub')
					->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id = ' . (int) $categoryId);

				if ($levels >= 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}

				// Add the subquery to the main query
				$query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where($categoryEquals);
			}
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);

			if (!empty($categoryId))
			{
				$type = $articles->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid ' . $type . ' (' . $categoryId . ')');
			}
		}

		// Filter by author
		$authorId = $articles->getState('filter.author_id');
		$authorWhere = '';

		if (is_numeric($authorId) && $authorId > 0)
		{
			$type = $articles->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by ' . $type . (int) $authorId;
		}
		elseif (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);

			if ($authorId)
			{
				$type = $articles->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
			}
		}

		// Filter by author alias
		$authorAlias = $articles->getState('filter.author_alias');
		$authorAliasWhere = '';

		if (is_string($authorAlias) && $authorAlias)
		{
			$type = $articles->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias ' . $type . $db->quote($authorAlias);
		}
		elseif (is_array($authorAlias))
		{
			$first = current($authorAlias);

			if (!empty($first))
			{
				JArrayHelper::toString($authorAlias);

				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->quote($alias);
				}

				$authorAlias = implode(',', $authorAlias);

				if ($authorAlias)
				{
					$type = $articles->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias .
						')';
				}
			}
		}

		if (!empty($authorWhere) && !empty($authorAliasWhere))
		{
			$query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere))
		{
			// If both are empty we don't want to add to the query
		}
		else
		{
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere . $authorAliasWhere);
		}

		// Define null and now dates
		$nullDate	= $db->quote($db->getNullDate());
		$nowDate	= $db->quote(JFactory::getDate()->toSql());

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content')))
		{
			$query	->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')')
				->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
		}

		// Filter by Date Range or Relative Date
		$dateFiltering = $articles->getState('filter.date_filtering', 'off');
		$dateField = $articles->getState('filter.date_field', 'a.created');

		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->quote($articles->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->quote($articles->getState('filter.end_date_range', $nullDate));
				$query->where(
					'(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField .
						' <= ' . $endDateRange . ')'
				);
				break;

			case 'relative':
				$relativeDate = (int) $articles->getState('filter.relative_date', 0);
				$query->where(
					$dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' .
						$relativeDate . ' DAY)'
				);
				break;

			case 'off':
			default:
				break;
		}

		// Process the filter for list views with user-entered filters
		$params = $articles->getState('params');

		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $articles->getState('list.filter')))
		{
			// Clean filter variable
			$filter = JString::strtolower($filter);
			$hitsFilter = (int) $filter;
			$filter = $db->quote('%' . $db->escape($filter, true) . '%', false);

			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where(
						'LOWER( CASE WHEN a.created_by_alias > ' . $db->quote(' ') .
							' THEN a.created_by_alias ELSE ua.name END ) LIKE ' . $filter . ' '
					);
					break;

				case 'hits':
					$query->where('a.hits >= ' . $hitsFilter . ' ');
					break;

				case 'title':
				default:
					// Default to 'title' if parameter is not valid
					$query->where('LOWER( a.title ) LIKE ' . $filter);
					break;
			}
		}

		// Filter by language
		if ($articles->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$cParams = JComponentHelper::getParams('com_minitekwall');

		// Group by
		if (!$tagIds && $cParams->get('enable_group_by', 1))
		{
			$query->group('a.id');
		}

		// Add the list ordering clause.
		$ordering = '' . $articles->getState('list.ordering', 'a.ordering') . ' ' . $articles->getState('list.direction', 'ASC').', a.id ' . $articles->getState('list.direction', 'ASC').'';
		$query->order($ordering);

		$db->setQuery($query, $articles->setState('list.start'), $articles->setState('list.limit'));

		if ($queryType == 'items') {
			// Load the results as a list of stdClass objects (see later for more options on retrieving data).
			$items = $db->loadObjectList();

			return $items;
		} else if ($queryType == 'count') {
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}

	// Get Joomla Categories
	public static function getJoomlaCategories($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();
		$cats = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');
		if (!$page || $page == 1) {
			$joomla_categories_limit = $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$joomla_categories_limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $joomla_categories_limit);

			$view = $app->input->get('view', '');
			$pagination = $app->input->get('pagination', '');
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append/Prepend
				$start = 0;
				$joomla_categories_limit = $start_limit + (($page - 1) * $joomla_categories_limit);
			}

			if ($start < $globalLimit) {
				if ($start + $pageLimit >= $globalLimit) {
					$joomla_categories_limit = $globalLimit - $start;
				}
			} else {
				$joomla_categories_limit = 0;
			}
		}

		$jc_include_categories_children = $data_source['jc_include_categories_children'];
		$jc_ordering = $data_source['jc_ordering'];
		$jc_ordering_direction = SORT_DESC;
		if (array_key_exists('jc_ordering_direction', $data_source))
		{
			if ($data_source['jc_ordering_direction'] == 'DESC')
			{
				$jc_ordering_direction = SORT_DESC;
			}
			else
			{
				$jc_ordering_direction = SORT_ASC;
			}
		}
		$jc_include_categories = $data_source['jc_include_categories'];

		$options = array();
		$options['countItems'] = true;
		$categories = JCategories::getInstance('Content', $options);
		$parent = $categories->get($jc_include_categories);

		if ($jc_include_categories_children)
		{
			$recursive = true;
		}
		else
		{
			$recursive = false;
		}

		$items = $parent->getChildren($recursive);
		$rows = array();
		$sort = array();

		foreach ($items as $key=>$item)
		{
			$rows[] = $item;
			$sort['id'][$key] = $item->id;
			$sort['title'][$key] = $item->title;
			$sort['created_time'][$key] = $item->created_time;
		}

		if ($items)
		{
			// Reorder array
			if ($jc_ordering == 'id')
			{
				array_multisort($sort['id'], $jc_ordering_direction, $rows);
			}
			else if ($jc_ordering == 'alpha')
			{
				array_multisort($sort['title'], $jc_ordering_direction, $sort['id'], $jc_ordering_direction, $rows);
			}
			else if ($jc_ordering == 'date')
			{
				array_multisort($sort['created_time'], $jc_ordering_direction, $sort['id'], $jc_ordering_direction, $rows);
			}

			// Slice array - limit
			$rows = array_slice($rows, $start, $joomla_categories_limit);

			return $rows;
		}
		else
		{
			return false;
		}
	}

	// Get Joomla Categories Count
	public static function getJoomlaCategoriesCount($data_source, $globalLimit)
	{
		$cats = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));

		$start = 0;
		$limit = $globalLimit;

		$jc_include_categories_children = $data_source['jc_include_categories_children'];
		$jc_include_categories = $data_source['jc_include_categories'];

		$options = array();
		$options['countItems'] = true;
		$categories = JCategories::getInstance('Content', $options);
		$parent = $categories->get($jc_include_categories);

		if ($jc_include_categories_children)
		{
			$recursive = true;
		}
		else
		{
			$recursive = false;
		}

		$items = $parent->getChildren($recursive);
		$rows = array();

		foreach ($items as $key=>$item)
		{
			$rows[] = $item;
		}

		if ($items)
		{
			// Slice array - limit
			$rows = array_slice($rows, $start, $limit);

			$itemCount = count($rows);
			return $itemCount;
		}
		else
		{
			return '0';
		}
	}
}
