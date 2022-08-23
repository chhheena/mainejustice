<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallLibSourceRSS
{
	// Get Items count from RSS feed
	public function getRSSItemsCount($data_source, $globalLimit)
	{
		$file_or_url = $data_source['rss_file_or_url'];
		$file_or_url = $this->resolveFile($file_or_url);

		if (!($temp = simplexml_load_file($file_or_url)))
      		return 0;

		$count = count($temp->channel->item);

		if ($count > $globalLimit)
		{
			$count = $globalLimit;
		}

		return $count;
	}

	// Get Items from RSS feed
	public function getRSSItems($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$app = JFactory::getApplication();

		$file_or_url = $data_source['rss_file_or_url'];
		$file_or_url = $this->resolveFile($file_or_url);

		if (!($temp = simplexml_load_file($file_or_url)))
      		return;

		$items = array();

		foreach ($temp->channel->item as $item)
		{
			$rss_item				= new stdClass();
			$rss_item->title 		= (string) $item->title;
			$rss_item->link  		= (string) $item->link;
			$rss_item->description	= (string) $item->description;
			$rss_item->author		= (string) $item->author;
			$rss_item->category		= (string) $item->category;
			$temp_date 				= (string) $item->pubDate;
			$rss_item->created  	= date('Y-m-d H:i:s', strtotime($temp_date));
			$rss_item->itemImageRaw	= isset($item->image) 
				? (isset($item->image->url) ? (string) $item->image->url : (string) $item->image) 
				: false;

			$items[] = $rss_item;
		}

		// Set the list start limit
		$page = $app->input->get('page', '', 'INT');

		if (!$page || $page == 1)
		{
			$limit	= $startLimit;
			$start = 0;
		}
		else
		{
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);
			$pagination = $app->input->get('pagination');

			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4'))
			{
				$start = 0;
				$limit = $start_limit + (($page - 1) * $limit);
			}

			if ($start < $globalLimit)
			{
				if ($start + $pageLimit >= $globalLimit)
				{
					$limit = $globalLimit - $start;
				}
			}
			else
			{
				$limit = 0;
			}
		}

		// Limit items according to pagination
		$items = array_slice($items, $start, $limit);

		return $items;
	}

	private function resolveFile($file_or_url)
	{
		if (!preg_match('|^https?:|', $file_or_url))
		{
			$feed_uri = $_SERVER['DOCUMENT_ROOT'] .'/'. $file_or_url;
		}
		else
		{
			$feed_uri = $file_or_url;
		}

		return $feed_uri;
	}
}
