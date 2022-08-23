<?php

/**
 * @title        Minitek Wall
 * @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

class MinitekWallLibSourceFolder
{
	// Search for files and directories recursively
	public static function rglob($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::rglob($dir . '/' . basename($pattern), $flags));
		}

		return $files;
	}

	// Get Images count from folder
	public function getFolderImagesCount($data_source, $globalLimit)
	{
		$folder = trim($data_source['image_folder'], '/');
		$directory = 'images/' . $folder . '/';
		$images = array();

		// Get all files within the root and subdirectories
		$files = self::rglob($directory . '*');
		$files = array_filter($files, 'is_file');

		// Check for allowed file extensions
		$allowed = array('gif', 'png', 'jpg', 'webp');

		foreach ($files as $file) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);

			if (in_array($ext, $allowed))
				$images[] = $file;
		}

		$count = count($images);

		if ($count > $globalLimit) {
			$count = $globalLimit;
		}

		return $count;
	}

	// Get Images from folder
	public function getFolderImages($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$folder = trim($data_source['image_folder'], '/');
		$directory = 'images/' . $folder . '/';
		$images = array();

		// Get all files within the root and subdirectories
		$files = self::rglob($directory . '*');
		$files = array_filter($files, 'is_file');

		// Check for allowed file extensions
		$allowed = array('gif', 'png', 'jpg', 'webp');

		foreach ($files as $file) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$dir = dirname($file);
			$img = array();

			if (in_array($ext, $allowed)) {
				$img['path'] = $file;
				$img['dir'] = $dir;
				$images[] = $img;
			}
		}

		foreach ($images as $key => $image) {
			$images[$key] = new stdClass();
			$images[$key]->path = $image['path'];
			$images[$key]->title = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($image['path']));
			$images[$key]->title = ucfirst(str_replace("-", " ", $images[$key]->title));
			$images[$key]->created = date('Y-m-d H:i:s', filemtime($image['path']));
			$images[$key]->category = basename($image['dir']);
			$images[$key]->category = ucfirst(str_replace("-", " ", $images[$key]->category));
		}

		// Ordering direction
		if ($data_source['fold_ordering_direction'] == 'ASC') {
			$dir = SORT_ASC;
		} else {
			$dir = SORT_DESC;
		}

		// Order by title and date
		if ($data_source['fold_ordering'] == 'title') {
			$title = array();
			$created = array();

			foreach ($images as $key => $row) {
				$title[$key] = $row->title;
				$created[$key] = $row->created;
			}

			array_multisort($title, $dir, $created, $dir, $images);
		}

		// Order by date and title
		if ($data_source['fold_ordering'] == 'created') {
			$created = array();
			$title = array();

			foreach ($images as $key => $row) {
				$created[$key] = $row->created;
				$title[$key] = $row->title;
			}

			array_multisort($created, $dir, $title, $dir, $images);
		}

		// Random order
		if ($data_source['fold_ordering'] == 'random') {
			shuffle($images);
		}

		// Set the list start limit
		$app = JFactory::getApplication();
		$page = $app->input->get('page', '', 'INT');

		if (!$page || $page == 1) {
			$limit	= $startLimit;
			$start = 0;
		} else {
			$start_limit = $startLimit;
			$limit = $pageLimit;
			$start = $start_limit + (($page - 2) * $limit);
			$pagination = $app->input->get('pagination');

			// Pagination: Append / Infinite
			if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) {
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

		// Limit items according to pagination
		$images = array_slice($images, $start, $limit);

		return $images;
	}
}
