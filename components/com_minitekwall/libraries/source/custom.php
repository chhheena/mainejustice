<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallLibSourceCustom
{
	// Get Custom Items
	public function getCustomItems($queryType, $data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

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
		$query = 'SELECT * FROM ' . $db->quoteName( '#__minitek_source_items');

		// Filter by state
		$query .= ' WHERE '.$db->quoteName('state').' = '.$db->quote('1');

		// Filter by group id
		$query.= ' AND '.$db->quoteName('groupid').' = '.$db->quote($data_source['custom_groupid']);

		// Filter by start and end dates
		$nullDate	= $db->quote($db->getNullDate());
		$nowDate	= $db->quote(JFactory::getDate()->toSql());

		$query .= ' AND ('.$db->quoteName('publish_up').' = '.$nullDate.' OR '.$db->quoteName('publish_up').' <= '.$nowDate.')';
		$query .= ' AND ('.$db->quoteName('publish_down').' = '.$nullDate.' OR '.$db->quoteName('publish_down').' >= '.$nowDate.')';

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query .= ' AND access IN ('.$groups.')';

		// Add the list ordering clause.
		$ordering = ''.$data_source['custom_ordering'].' '.$data_source['custom_ordering_direction'].', id '.$data_source['custom_ordering_direction'].'';
		$query .= ' ORDER BY '.$ordering;

		$db->setQuery( $query, $start, $limit );

		if ($queryType == 'items')
		{
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
		}
		else if ($queryType == 'count')
		{
			$db->query();
			$itemCount = $db->getNumRows();

			return $itemCount;
		}
	}
}
