<?php

/**
 * @title        Minitek Wall
 * @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

class MinitekWallLibBaseOptions
{
	var $utilities = null;

	function __construct()
	{
		$this->utilities = new MinitekWallLibUtilities;

		return;
	}

	public function getRSSDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'rss');

		foreach ($items as &$item) {
			// Content type
			$item->itemType = JText::_('COM_MINITEKWALL_' . $source['rss_title']);

			// Image
			if ($detailBoxParams['images']) {
				$introtext_temp = strip_tags($item->description, '<img>');
				preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);

				$src = false;
				if ($new_image && function_exists('mb_convert_encoding')) {
					$new_image[0] = mb_convert_encoding($new_image[0], 'HTML-ENTITIES', "UTF-8");
					$doc = new DOMDocument();
					$doc->loadHTML($new_image[0]);
					$xpath = new DOMXPath($doc);
					$src = $xpath->evaluate("string(//img/@src)");
				}
				if ($src) {
					$item->itemImageRaw = $src;
				}

				// Fallback image
				if (!$item->itemImageRaw && $detailBoxParams['fallback_image']) {
					$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
				}

				// Final image
				$item->itemImage =  $item->itemImageRaw;
				$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

				// Crop images
				if (
					$detailBoxParams['crop_images'] &&
					$item->itemImage &&
					$ext !== 'webp' &&
					$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
				) {
					$item->itemImage = $image;
				}

				// Experimental - Make sure that we don't have a relative image path
				if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
					$item->itemImage = JURI::root() . '' . $item->itemImage;
				}
			}

			// Title
			$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
			$item->itemTitleRaw = $item->title;
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
				$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
			}

			// Link
			$item->itemLink = $item->link;

			// Introtext
			$item->itemIntrotext = $item->description;
			$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotext);
			$item->hover_itemIntrotext = $item->description;
			$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->hover_itemIntrotext);

			if ($detailBoxParams['detailBoxStripTags']) {
				$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
				$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
			}

			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
				$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
				$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
			}

			// Date
			$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
				$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
			}
			$item->itemDateRaw = $item->created;

			// Author
			$author = $item->author;
			$item->itemAuthorRaw = $author;
			$item->itemAuthor = $item->itemAuthorRaw;

			// Category
			$item->itemCategoryRaw = $item->category;
			$item->itemCategory = $item->itemCategoryRaw;
		}

		return $items;
	}

	public function getFolderDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'folder');

		foreach ($items as &$item) {
			// Content type
			$item->itemType = JText::_('COM_MINITEKWALL_' . $source['fold_title']);

			// Image
			if ($detailBoxParams['images']) {
				$item->itemImageRaw = $item->path;
				$item->itemImage = $item->itemImageRaw;
				$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

				if (
					$detailBoxParams['crop_images'] &&
					$item->itemImage &&
					$ext !== 'webp' &&
					$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
				) {
					$item->itemImage = $image;
				}

				// Experimental - Make sure that we don't have a relative image path
				if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
					$item->itemImage = JURI::root() . '' . $item->itemImage;
				}
			}

			// Title
			$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
			$item->itemTitleRaw = $item->title;
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
				$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
			}

			// Date
			$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
				$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
			}
			$item->itemDateRaw = $item->created;

			// Category
			$item->itemCategoryRaw = $item->category;
			$item->itemCategory = $item->itemCategoryRaw;
		}

		return $items;
	}

	public function getEasyblogDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'easyblog');
		$easyblog_source = new MinitekWallLibSourceEasyblog;
		$easyblog_mode = 'eba';
		$items = EB::formatter('list', $items);

		foreach ($items as &$item) {
			if ($easyblog_mode == 'eba') {
				$item->itemID = $item->id;
				$item->itemStart = $item->publish_up;
				$item->itemModified = $item->modified;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['eb_title']);

				// Image
				if ($detailBoxParams['images']) {
					$item->itemImageRaw = false;

					if ($source['eb_image_type'] == '1' && $item->hasImage()) // Article image
					{
						$item->itemImageRaw = $item->getImage('large');
					} else // Inline image
					{
						if ($item->intro) {
							$introtext_temp = strip_tags($item->intro, '<img>');
							preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);
							if (!$new_image) {
								$introtext_temp = strip_tags($item->content, '<img>');
								preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);
							}
						} else {
							$introtext_temp = strip_tags($item->content, '<img>');
							preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);
						}

						$src = false;
						if ($new_image && function_exists('mb_convert_encoding')) {
							$new_image[0] = mb_convert_encoding($new_image[0], 'HTML-ENTITIES', "UTF-8");
							$doc = new DOMDocument();
							$doc->loadHTML($new_image[0]);
							$xpath = new DOMXPath($doc);
							$src = $xpath->evaluate("string(//img/@src)");
						}
						if ($src) {
							$item->itemImageRaw = $src;
						}
					}

					// Fallback image
					if (!$item->itemImageRaw && $detailBoxParams['fallback_image']) {
						$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
					}

					// Final image
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					// Crop images
					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $item->getPermalink();

				// Introtext
				$item->itemIntrotext = $item->getIntro(true);
				$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotext);
				$item->hover_itemIntrotext = $item->getIntro(true);
				$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->hover_itemIntrotext);

				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Categories
				$item->itemCategoriesRaw = $item->categories;
				$item->itemCategory = '';
				$item->itemCategories = '';
				foreach ($item->categories as $key => $category) {
					$item->itemCategory .= '<a href="' . $category->getPermalink() . '">';
					$item->itemCategory .= $category->getTitle();
					$item->itemCategory .= '</a>';
					$item->itemCategories .= $this->utilities->cleanName($category->getTitle()) . ' ';
					if ($key < count($item->itemCategoriesRaw) - 1) {
						$item->itemCategory .= '&nbsp;&#124;&nbsp;';
					}
				}

				// Author
				$author = $item->getAuthor();
				$item->itemAuthorRaw = $author->getName();
				$item->itemAuthorLink = $author->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Tags
				$item->itemTags = $item->getTags();

				if ($item->itemTags)
				{
					$item->itemTagsLayout = '<ul class="tags inline">';

					foreach ($item->itemTags as $i => $tag)
					{
						$item->itemTagsLayout .= '<li class="tag-'.$tag->id.' tag-list'.$i.'" itemprop="keywords">';
						$item->itemTagsLayout .= '<a href="'.$tag->getPermalink().'" class="label label-info">';
						$item->itemTagsLayout .= $tag->title;
						$item->itemTagsLayout .= '</a>';
						$item->itemTagsLayout .= '</li>';
					}

					$item->itemTagsLayout .= '</ul>';
				}

				// Hits
				$item->itemHits = $item->hits;
			}
		}

		return $items;
	}

	public function getEasysocialDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'easysocial');
		$easysocial_source = new MinitekWallLibSourceEasysocial;
		$easysocial_mode = $source['easysocial_mode'];

		foreach ($items as &$item) {
			if ($easysocial_mode == 'esu') {
				$es_user = ES::user($item->id);
				$item->itemID = $item->id;
				$item->itemPoints = $es_user->getPoints();
				$item->itemFriends = $es_user->getTotalFriends();

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['esu_title']);

				// Image
				$user_name = $es_user->getName();
				if ($detailBoxParams['images']) {
					if ($es_user->getAvatar()) {
						$item->itemImageRaw = $es_user->getAvatar(SOCIAL_AVATAR_LARGE);
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $user_name, $type = 'user')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = $user_name;
				$item->itemTitle = $this->utilities->wordLimit($user_name, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($user_name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $es_user->getPermalink();

				// Date
				$item->itemDate = JHTML::_('date', $item->registerDate, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->registerDate, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->registerDate;

				// Count
				$item->itemCount = $es_user->getTotalFriends();
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_FRIEND');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_FRIENDS');
				}
			} else if ($easysocial_mode == 'esg') {
				$es_group = ES::group($item->id);
				$item->itemID = $item->id;
				$item->itemMembers = $es_group->getTotalMembers();

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['esg_title']);

				// Image
				$group_name = $es_group->getName();
				if ($detailBoxParams['images']) {
					if ($es_group->getAvatar()) {
						$item->itemImageRaw = $es_group->getAvatar(SOCIAL_AVATAR_LARGE);
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $group_name, $type = 'group')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = $group_name;
				$item->itemTitle = $this->utilities->wordLimit($group_name, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($group_name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $es_group->getPermalink();

				// Introtext
				$item->itemIntrotextRaw = JText::_($item->description);
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$item->itemCategoryRaw = $es_group->getCategory()->getTitle();
				$item->itemCategoryLink = $es_group->getCategory()->getPermalink();
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = ES::user($item->creator_uid)->getName();
				$item->itemAuthorLink = ES::user($item->creator_uid)->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;

				// Count
				$item->itemCount = $item->itemMembers;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_MEMBER');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_MEMBERS');
				}
			} else if ($easysocial_mode == 'ese') {
				$es_event = ES::event($item->id);
				$item->itemID = $item->id;
				$item->itemFinish = $item->enddate;
				$item->itemMembers = $es_event->getTotalMembers();

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['ese_title']);

				// Image
				$event_name = $es_event->getName();
				if ($detailBoxParams['images']) {
					if (!isset($source['ese_image_type']) || $source['ese_image_type'] == 'avatar') {
						if ($es_event->getAvatar()) {
							$item->itemImageRaw = $es_event->getAvatar(SOCIAL_AVATAR_LARGE);
						}
					} else {
						if ($es_event->getCover()) {
							$item->itemImageRaw = $es_event->getCover('large');
						}
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $event_name, $type = 'event')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = $event_name;
				$item->itemTitle = $this->utilities->wordLimit($event_name, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($event_name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $es_event->getPermalink();

				// Introtext
				$item->itemIntrotextRaw = JText::_($item->description);
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {

					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->startdate, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->startdate, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->startdate;

				// Category
				$item->itemCategoryRaw = $es_event->getCategory()->getTitle();
				$item->itemCategoryLink = $es_event->getCategory()->getPermalink();
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = ES::user($item->creator_uid)->getName();
				$item->itemAuthorLink = ES::user($item->creator_uid)->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;

				// Count
				$item->itemCount = $item->itemMembers;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ATTENDEE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ATTENDEES');
				}
			} else if ($easysocial_mode == 'esp') {
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['esp_title']);

				// Image
				$photo_name = $item->title;
				if ($detailBoxParams['images']) {
					if ($item->getSource('original')) {
						$item->itemImageRaw = $item->getSource('original');
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $photo_name, $type = 'photo')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = JText::_($photo_name);
				$item->itemTitle = $this->utilities->wordLimit($item->itemTitleRaw, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->itemTitleRaw, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $item->getPermalink();

				// Introtext
				$item->itemIntrotextRaw = JText::_($item->caption);
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {

					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$album = $item->getAlbum();
				$item->itemCategoryLink = $album->getPermalink();
				$item->itemCategoryRaw = JText::_($album->title);
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = ES::user($item->user_id)->getName();
				$item->itemAuthorLink = ES::user($item->user_id)->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';
			} else if ($easysocial_mode == 'esa') {
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['esa_title']);

				// Image
				if ($detailBoxParams['images']) {
					if ($item->getCover('original')) {
						$item->itemImageRaw = $item->getCover('original');
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title, $type = 'album')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = JText::_($item->title);
				$item->itemTitle = $this->utilities->wordLimit($item->itemTitleRaw, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = JText::_($this->utilities->wordLimit($item->itemTitleRaw, $hoverBoxParams['hoverBoxTitleLimit']));
				}

				// Link
				$item->itemLink = $item->getPermalink();

				// Introtext
				$item->itemIntrotextRaw = JText::_($item->caption);
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Author
				$item->itemAuthorRaw = ES::user($item->user_id)->getName();
				$item->itemAuthorLink = ES::user($item->user_id)->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;
			} else if ($easysocial_mode == 'esv') {
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['esv_title']);

				// Image
				if ($detailBoxParams['images']) {
					if ($item->thumbnail) {
						$item->itemImageRaw = $item->thumbnail;
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title, $type = 'video')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitleRaw = $item->title;
				$item->itemTitle = $this->utilities->wordLimit($item->itemTitleRaw, $detailBoxParams['detailBoxTitleLimit']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->itemTitleRaw, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $item->getPermalink();

				// Introtext
				$item->itemIntrotextRaw = $item->description;
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->itemIntrotextRaw);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$item->itemCategoryLink = $item->getCategory()->getPermalink();
				$item->itemCategoryRaw = $item->getCategory()->title;
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = ES::user($item->user_id)->getName();
				$item->itemAuthorLink = ES::user($item->user_id)->getPermalink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;
			}
		}

		return $items;
	}

	public function getVirtuemartDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'virtuemart');
		$virtuemart_source = new MinitekWallLibSourceVirtuemart;
		$virtuemart_mode = 'vmp';

		foreach ($items as &$item) {
			if ($virtuemart_mode == 'vmp') {
				$item->itemID = $item->virtuemart_product_id;
				$item->itemModified = $item->modified_on;
				$item->itemSales = $item->product_sales;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['vmp_title']);

				// Image
				if ($detailBoxParams['images']) {
					$item->itemImageRaw = $item->images[0]->file_url;

					// Fallback image
					if (!$item->itemImageRaw && $detailBoxParams['fallback_image']) {
						$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
					}

					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->product_name)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->product_name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->product_name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->product_name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $item->virtuemart_product_id . '&virtuemart_category_id=' . $item->virtuemart_category_id . '&Itemid=' . $source['vmp_itemid']);

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->product_s_desc);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->product_s_desc);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->product_s_desc);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->product_s_desc);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created_on, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created_on, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created_on;

				// Categories
				$item->itemCategoriesRaw = $item->categoryItem;
				$item->itemCategory = '';
				$item->itemCategories = '';
				foreach ($item->itemCategoriesRaw as $key => $category) {
					$item->itemCategory .= '<a href="' . JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category["virtuemart_category_id"] . '&Itemid=' . $source['vmp_itemid']) . '">';
					$item->itemCategory .= $category['category_name'];
					$item->itemCategory .= '</a>';
					$item->itemCategories .= $this->utilities->cleanName($category['category_name']) . ' ';
					if ($key < count($item->itemCategoriesRaw) - 1) {
						$item->itemCategory .= '&nbsp;&#124;&nbsp;';
					}
				}

				// Manufacturers
				$manufacturerModel = VmModel::getModel('Manufacturer');
				$item->itemAuthorsRaw = $item->virtuemart_manufacturer_id;
				$item->itemAuthor = '';
				$item->itemAuthors = '';
				foreach ($item->itemAuthorsRaw as $key => $itemManufacturer) {
					$manufacturer = $manufacturerModel->getManufacturer((int)$itemManufacturer);
					$item->itemAuthor .= $manufacturer->mf_name;
					$item->itemAuthors .= $manufacturer->mf_name . ' ';
					if ($key < count($item->itemAuthorsRaw) - 1) {
						$item->itemAuthor .= '&nbsp;&#124;&nbsp;';
					}
				}

				// Sales
				$item->itemCount = $item->product_sales;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_SALE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_SALES');
				}

				// Price
				$currency = CurrencyDisplay::getInstance();
				switch ($source['vmp_price_type']) {
					case "prices['salesPrice']":
						$item->itemPrice = $currency->createPriceDiv('salesPrice', '', $item->prices);
						break;
					case "prices['discountedPriceWithoutTax']":
						if ($item->prices['discountedPriceWithoutTax'] != $item->prices['priceWithoutTax']) {
							$item->itemPrice = $currency->createPriceDiv('discountedPriceWithoutTax', '', $item->prices);
						} else {
							$item->itemPrice = $currency->createPriceDiv('priceWithoutTax', '', $item->prices);
						}
						break;
					case "prices['basePrice']":
						$item->itemPrice = $currency->createPriceDiv('basePrice', '', $item->prices);
						if (round($item->prices['basePrice'], $currency->_priceConfig['basePriceVariant'][1]) != $item->prices['basePriceVariant']) {
							$item->itemPrice = $currency->createPriceDiv('basePriceVariant', '', $item->prices);
						}
						break;
					case "prices['basePriceWithTax']":
						$item->itemPrice = $currency->createPriceDiv('basePriceWithTax', '', $item->prices);
						break;
					default:
						$item->itemPrice = $currency->createPriceDiv('salesPrice', '', $item->prices, FALSE, FALSE, 1.0, TRUE);
						break;
				}
			}
		}

		return $items;
	}

	public function getJomsocialDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'jomsocial');
		$jomsocial_source = new MinitekWallLibSourceJomsocial;
		$jomsocial_mode = $source['jomsocial_mode'];

		foreach ($items as &$item) {
			if ($jomsocial_mode == 'jsu') {
				$js_user = CFactory::getUser($item->userid);
				$item->itemID = $item->userid;
				$item->itemPoints = $item->points;
				$item->itemFriends = $item->friendcount;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jsu_title']);

				// Image
				$user_name = JFactory::getUser($item->userid)->get('name');
				if ($detailBoxParams['images']) {
					if ($js_user->getAvatar()) {
						$item->itemImageRaw = $js_user->getAvatar();
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $user_name, $type = 'user')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($user_name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $user_name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($user_name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $js_user->getProfileLink();

				// Introtext
				$user_description = $js_user->getInfo('FIELD_ABOUTME');
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $user_description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $user_description);
				}


				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $user_description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $user_description);
				}

				// Date
				$user_date = JFactory::getUser($item->userid)->get('registerDate');
				$item->itemDate = JHTML::_('date', $user_date, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $user_date, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $user_date;

				// Category
				$user_city = JText::_($js_user->getInfo('FIELD_CITY'));
				if ($user_city) {
					$item->itemCategoryRaw = $user_city;
					$item->itemCategory = $item->itemCategoryRaw;
				}

				// Location
				$user_country = JText::_($js_user->getInfo('FIELD_COUNTRY'));
				if ($user_country) {
					$item->itemLocationRaw = $user_country;
					$item->itemLocation = $item->itemLocationRaw;
				}

				// Hits
				$item->itemHits = $item->view;
			} else if ($jomsocial_mode == 'jsg') {
				$group = JTable::getInstance('Group', 'CTable');
				$group->bind($item);
				$item->itemID = $item->id;
				$item->itemMembers = $item->membercount;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jsg_title']);

				// Image
				if ($detailBoxParams['images']) {
					if ($group->getLargeAvatar()) {
						$item->itemImageRaw = $group->getLargeAvatar();
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->name, $type = 'group')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $group->getLink();

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$category = JTable::getInstance('GroupCategory', 'CTable');
				$category->load($item->categoryid);
				$item->itemCategoryRaw = $category->name;
				$item->itemCategoryLink = urldecode(CRoute::_('index.php?option=com_community&view=groups&task=display&categoryid=' . $item->categoryid));
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = CFactory::getUser($item->ownerid)->name;
				$item->itemAuthorLink = CFactory::getUser($item->ownerid)->getProfileLink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;

				// Count
				$item->itemCount = $item->membercount;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_MEMBER');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_MEMBERS');
				}
			} else if ($jomsocial_mode == 'jse') {
				$event = JTable::getInstance('Event', 'CTable');
				$event->bind($item);
				$item->itemID = $item->id;
				$item->itemFinish = $item->enddate;
				$item->itemConfirmed = $item->confirmedcount;
				$item->itemTickets = $item->ticket;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jse_title']);

				// Image
				if ($detailBoxParams['images']) {
					if ($event->getCover()) {
						$item->itemImageRaw = $event->getCover();
					}
					$item->itemImage = $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title, $type = 'event')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $event->getLink();

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->startdate, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->startdate, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->startdate;

				// Category
				$category = JTable::getInstance('EventCategory', 'CTable');
				$category->load($item->catid);
				$item->itemCategoryRaw = $category->name;
				$item->itemCategoryLink = urldecode(CRoute::_('index.php?option=com_community&view=events&categoryid=' . $item->catid));
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Location
				$item->itemLocationRaw = $item->location;
				$item->itemLocation = $item->location;

				// Author
				$item->itemAuthorRaw = CFactory::getUser($item->creator)->name;
				$item->itemAuthorLink = CFactory::getUser($item->creator)->getProfileLink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;

				// Count
				$item->itemCount = $item->confirmedcount;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ATTENDEE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ATTENDEES');
				}
			} else if ($jomsocial_mode == 'jsp') {
				$photo = JTable::getInstance('Photo', 'CTable');
				$photo->bind($item);
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jsp_title']);

				// Image
				if ($detailBoxParams['images']) {
					if ($photo->getImageURI('original')) {
						$item->itemImageRaw = $photo->getImageURI('original');
					}
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->caption, $type = 'photo')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->caption, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->caption;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->caption, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $photo->getPhotoLink();

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {

					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$album = JTable::getInstance('Album', 'CTable');
				$album->load($item->albumid);
				$albumType = $album->type;
				$albumGroupid = $album->groupid;
				$albumCreator = $album->creator;
				if ($albumType == 'group') {
					$item->itemCategoryLink = CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $item->albumid . '&groupid=' . $albumGroupid);
				} else {
					$item->itemCategoryLink = CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $item->albumid . '&userid=' . $albumCreator);
				}
				$item->itemCategoryRaw = $album->name;
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = CFactory::getUser($item->creator)->name;
				$item->itemAuthorLink = CFactory::getUser($item->creator)->getProfileLink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;
			} else if ($jomsocial_mode == 'jsa') {
				$album = JTable::getInstance('Album', 'CTable');
				$album->bind($item);
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jsa_title']);

				// Image
				if ($detailBoxParams['images']) {
					$item->itemImageRaw = preg_replace('/thumb_/', '', $album->getCoverThumbPath());
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->name, $type = 'album')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $album->getURI();

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Author
				$item->itemAuthorRaw = CFactory::getUser($item->creator)->name;
				$item->itemAuthorLink = CFactory::getUser($item->creator)->getProfileLink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;
			} else if ($jomsocial_mode == 'jsv') {
				$video = JTable::getInstance('Video', 'CTable');
				$video->bind($item);
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jsv_title']);

				// Image
				if ($detailBoxParams['images']) {
					$item->itemImageRaw = $video->getThumbnail();
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title, $type = 'video')
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = $video->getPermalink();

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$category = JTable::getInstance('VideosCategory', 'CTable');
				$category->load($item->category_id);
				$item->itemCategoryLink = CRoute::_('index.php?option=com_community&view=videos&task=display&catid=' . $item->category_id);
				$item->itemCategoryRaw = $category->name;
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = CFactory::getUser($item->creator)->name;
				$item->itemAuthorLink = CFactory::getUser($item->creator)->getProfileLink();
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;
			}
		}

		return $items;
	}

	public function getK2DisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$model = K2Model::getInstance('Item', 'K2Model');
		$source = $this->utilities->getSource($widgetID, 'k2');
		$k2_mode = $source['k2_mode'];

		foreach ($items as &$item) {
			if ($k2_mode == 'k2i') {
				$item->itemID = $item->id;
				$item->itemStart = $item->publish_up;
				$item->itemOrdering = $item->ordering;
				$item->itemRating = 0;
				if (isset($item->rating)) {
					$item->itemRating = $item->rating;
				}
				$item->itemComments = 0;
				if (isset($item->numOfComments)) {
					$item->itemComments = $item->numOfComments;
				}
				$item->itemModified = $item->modified;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['k2i_title']);

				// Image
				if ($detailBoxParams['images']) {
					$componentParams = JComponentHelper::getParams('com_k2');
					$date = JFactory::getDate($item->modified);
					$timestamp = '?t=' . $date->toUnix();
					$imageSize = $source['k2i_image_size'];

					$item->itemImageRaw = NULL;

					if (JFile::exists(JPATH_SITE . DS . 'media' . DS . 'k2' . DS . 'items' . DS . 'cache' . DS . md5("Image" . $item->id) . '_' . $imageSize . '.jpg')) {
						$item->itemImageRaw  = 'media/k2/items/cache/' . md5("Image" . $item->id) . '_' . $imageSize . '.jpg';
						if ($componentParams->get('imageTimestamp')) {
							$item->itemImageRaw .= $timestamp;
						}
					}

					// Image fallback
					if (!$item->itemImageRaw) {
						$item_text = $item->introtext . ' ' . $item->fulltext;
						$introtext_temp = strip_tags($item_text, '<img>');
						preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);
						$src = false;

						if ($new_image && function_exists('mb_convert_encoding')) {
							$new_image[0] = mb_convert_encoding($new_image[0], 'HTML-ENTITIES', "UTF-8");
							$doc = new DOMDocument();
							$doc->loadHTML($new_image[0]);
							$xpath = new DOMXPath($doc);
							$src = $xpath->evaluate("string(//img/@src)");
						}
						if ($src) {
							$item->itemImageRaw = $src;
						} else {
							if (array_key_exists('fallback_image', $detailBoxParams) && $detailBoxParams['fallback_image']) {
								$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
							}
						}
					}

					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id . ':' . urlencode($item->alias), $item->catid . ':' . urlencode($item->categoryalias))));

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$item->itemCategoriesRaw = array(
					array("id" => $item->catid, "category_name" => $item->categoryname)
				);
				$maxlevel = isset($source['k2i_filter_levels']) ? (int)$source['k2i_filter_levels'] : 0;
				$parents = $this->utilities->getK2ParentCats($item->catid, $maxlevel);
				if ($parents) {
					foreach ($parents as $parent) {
						array_push($item->itemCategoriesRaw, array("id" => $parent["id"], "category_name" => $parent["category_name"]));
					}
				}
				$item->itemCategoryRaw = $item->categoryname;
				$item->itemCategoryLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid . ':' . urlencode($item->categoryalias))));
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				if (!empty($item->created_by_alias)) {
					$item->itemAuthorRaw = $item->created_by_alias;
					$item->itemAuthorLink = Juri::root(true);
				} else {
					$author = JFactory::getUser($item->created_by);
					$item->itemAuthorRaw = $author->name;
					$item->itemAuthorLink = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
				}
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';

				// Hits
				$item->itemHits = $item->hits;

				// Tags
				$tags = $model->getItemTags($item->id);
				$item->itemTags = $tags;
				foreach ($item->itemTags as $itemTag) {
					$itemTag->title = $itemTag->name;
				}
			} else if ($k2_mode == 'k2c') {
				$item->itemID = $item->id;
				$item->itemOrdering = $item->ordering;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['k2c_title']);

				// Image
				if ($detailBoxParams['images']) {
					$item->itemImageRaw = NULL;

					if (JFile::exists(JPATH_SITE . DS . 'media' . DS . 'k2' . DS . 'categories' . DS . $item->image)) {
						$item->itemImageRaw = 'media/k2/categories/' . $item->image;
					}

					/*// Fallback image
					else
					{
						$item->itemImage = JURI::base().'components/com_minitekwall/assets/images/category.jpg';
					}*/

					$item->itemImage = $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->name)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->id . ':' . urlencode($item->alias))));

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Count
				$this->k2_source = new MinitekWallLibSourceK2;
				$item->itemCount = $this->k2_source->countCategoryItems($item->id);
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLES');
				}
			} else if ($k2_mode == 'k2a') {
				$item->itemID = $item->created_by;

				$author = JFactory::getUser($item->created_by);
				$db = JFactory::getDBO();
				$query = "SELECT " . $db->quoteName('id') . ", " . $db->quoteName('gender') . ", " . $db->quoteName('description') . ", " . $db->quoteName('image') . ", " . $db->quoteName('url') . ", " . $db->quoteName('group') . ", " . $db->quoteName('plugins') . " FROM " . $db->quoteName('#__k2_users') . " WHERE " . $db->quoteName('userID') . " = " . $db->quote((int)$author->id);
				$db->setQuery($query);
				$author->profile = $db->loadObject();

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['k2a_title']);

				// Image
				if ($detailBoxParams['images']) {
					$componentParams = JComponentHelper::getParams('com_k2');
					$item->itemImageRaw = NULL;

					if (isset($author->profile->image) && $author->profile->image) {
						$item->itemImageRaw = K2HelperUtilities::getAvatar($author->id, $author->email, $componentParams->get('userImageWidth'));
						$item->itemImageRaw = str_replace(JURI::root(true), '', $item->itemImageRaw);
						$item->itemImageRaw = trim($item->itemImageRaw, '/');
					}

					/* // Image fallback
					else
					{
						$author->itemImageRaw = JURI::base().'components/com_minitekwall/assets/images/author.jpg';
					}*/

					$item->itemImage = $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $author->name)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($author->name, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $author->name;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($author->name, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Link
				$item->itemLink = JRoute::_(K2HelperRoute::getUserRoute($author->id));

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					if ($author->profile) {
						$item->itemIntrotext = preg_replace('/\{.*\}/', '', $author->profile->description);
						$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
						$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
					}
				} else {
					if ($author->profile) {
						$item->itemIntrotext = preg_replace('/\{.*\}/', '', $author->profile->description);
					}
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					if ($author->profile) {
						$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $author->profile->description);
						$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
						$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
					}
				} else {
					if ($author->profile) {
						$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $author->profile->description);
					}
				}

				// Count
				$this->k2_source = new MinitekWallLibSourceK2;
				$item->itemCount = $this->k2_source->countAuthorItems($author->id, $source['k2a_category_id']);
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLES');
				}
			}
		}

		return $items;
	}

	public function getJoomlaDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		$com_path = JPATH_SITE . '/components/com_content/';
		if (!class_exists('ContentHelperRoute')) {
			require_once $com_path . 'helpers/route.php';
		}

		// Get source params
		$source = $this->utilities->getSource($widgetID, 'joomla');
		$joomla_mode = $source['joomla_mode'];

		foreach ($items as &$item) {
			if ($joomla_mode == 'ja') {
				$item->itemID = $item->id;
				$item->itemOrdering = $item->ordering;
				$item->itemFOrdering = $item->fordering;
				$item->itemAlias = $item->alias;
				$item->itemModified = $item->modified;
				$item->itemStart = $item->publish_up;
				$item->itemFinish = $item->publish_down;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['ja_title']);

				// Image
				if ($detailBoxParams['images']) {
					$images = json_decode($item->images, true);
					if ($source['ja_image_type'] == 'introtext') {
						$item->itemImageRaw = $images['image_intro'];
					} else if ($source['ja_image_type'] == 'fulltext') {
						$item->itemImageRaw = $images['image_fulltext'];
					} else if ($source['ja_image_type'] == 'inline') {
						$introtext_temp = strip_tags($item->introtext, '<img>');
						preg_match('/<img[^>]+>/i', $introtext_temp, $new_image);
						$src = false;

						if ($new_image && function_exists('mb_convert_encoding')) {
							$new_image[0] = mb_convert_encoding($new_image[0], 'HTML-ENTITIES', "UTF-8");
							$doc = new DOMDocument();
							$doc->loadHTML($new_image[0]);
							$xpath = new DOMXPath($doc);
							$src = $xpath->evaluate("string(//img/@src)");
						}
						if ($src) {
							$item->itemImageRaw = $src;
						} else {
							$item->itemImageRaw = $images['image_intro'];
						}
					}

					// Image fallback
					if (!$item->itemImageRaw) {
						if (array_key_exists('fallback_image', $detailBoxParams) && $detailBoxParams['fallback_image']) {
							$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
						}
					}

					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Links
				$item->slug = $item->id . ':' . $item->alias;
				$item->catslug = $item->catid ? $item->catid . ':' . $item->category_alias : $item->catid;
				$item->itemLink = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
				$item->itemCategoryLink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->introtext);
				}

				// Date
				$ja_date_field = $source['ja_date_field'];
				$item->itemDate = JHTML::_('date', $item->$ja_date_field, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->$ja_date_field, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created;

				// Category
				$item->itemCategoriesRaw = array(
					array("id" => $item->catid, "category_name" => $item->category_title)
				);
				$maxlevel = isset($source['ja_filter_levels']) ? (int)$source['ja_filter_levels'] : 0;
				$parents = $this->utilities->getJoomlaParentCats($item->catid, $maxlevel);
				if ($parents) {
					foreach ($parents as $parent) {
						array_push($item->itemCategoriesRaw, array("id" => $parent["id"], "category_name" => $parent["category_name"]));
					}
				}
				$item->itemCategoryRaw = $item->category_title;
				$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';

				// Author
				$item->itemAuthorRaw = $item->author;
				$item->itemAuthor = $item->itemAuthorRaw;

				// Hits
				$item->itemHits = $item->hits;

				// Tags
				$item_tags = new JHelperTags;
				$item->itemTags = $item_tags->getItemTags('com_content.article', $item->id);
				$item->tagLayout = new JLayoutFile('joomla.content.tags');
				$item->itemTagsLayout = $item->tagLayout->render($item->itemTags);
			} else if ($joomla_mode == 'jc') {
				$item->itemID = $item->id;

				// Content type
				$item->itemType = JText::_('COM_MINITEKWALL_' . $source['jc_title']);

				// Image
				if ($detailBoxParams['images']) {
					$cat_params = json_decode($item->params, true);
					$item->itemImageRaw = $cat_params['image'];

					// Define new images
					$item->itemImage =  $item->itemImageRaw;
					$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

					// Image fallback
					/*if (!$item->itemImageRaw)
					{
						$item->itemImage = JURI::base().'components/com_minitekwall/assets/images/category.jpg';
					}*/

					// Crop images
					if (
						$detailBoxParams['crop_images'] &&
						$item->itemImage &&
						$ext !== 'webp' &&
						$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
					) {
						$item->itemImage = $image;
					}

					// Experimental - Make sure that we don't have a relative image path
					if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
						$item->itemImage = JURI::root() . '' . $item->itemImage;
					}
				}

				// Title
				$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
				$item->itemTitleRaw = $item->title;
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
					$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
				}

				// Links
				$item->itemLink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->id));

				// Introtext
				if ($detailBoxParams['detailBoxStripTags']) {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
					$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
				} else {
					$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
					$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
					$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
				} else {
					$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				}

				// Date
				$item->itemDate = JHTML::_('date', $item->created_time, $detailBoxParams['detailBoxDateFormat']);
				if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
					$item->hover_itemDate = JHTML::_('date', $item->created_time, $hoverBoxParams['hoverBoxDateFormat']);
				}
				$item->itemDateRaw = $item->created_time;

				// Author
				$item->itemAuthorRaw = $item->created_user_id;
				$item->itemAuthor = JFactory::getUser($item->itemAuthorRaw)->name;

				// Count
				$item->itemCount = $item->numitems;
				if ($item->itemCount == 1) {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLE');
				} else {
					$item->itemCount = $item->itemCount . '&nbsp;' . JText::_('COM_MINITEKWALL_ARTICLES');
				}

				// Hits
				$item->itemHits = $item->hits;
			}
		}

		return $items;
	}

	public function getCustomDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		// Get source params
		$source = $this->utilities->getSource($widgetID, 'custom');

		foreach ($items as &$item) {
			$item->itemID = $item->id;
			$item->itemOrdering = $item->ordering;
			$item->itemModified = $item->modified;
			$item->itemStart = $item->publish_up;
			$item->itemFinish = $item->publish_down;

			// Content type
			$item->itemType = JText::_('COM_MINITEKWALL_' . $source['custom_title']);

			// Image
			if ($detailBoxParams['images']) {
				$images = json_decode($item->images, true);
				$item->itemImageRaw = $images['image'];

				// Image fallback
				if (!$item->itemImageRaw) {
					if (array_key_exists('fallback_image', $detailBoxParams) && $detailBoxParams['fallback_image']) {
						$item->itemImageRaw = JURI::root() . '' . $detailBoxParams['fallback_image'];
					}
				}

				$item->itemImage =  $item->itemImageRaw;
				$ext = pathinfo($item->itemImageRaw, PATHINFO_EXTENSION);

				if (
					$detailBoxParams['crop_images'] &&
					$item->itemImage &&
					$ext !== 'webp' &&
					$image = $this->utilities->renderImages($item->itemImage, $detailBoxParams['image_width'], $detailBoxParams['image_height'], $item->title)
				) {
					$item->itemImage = $image;
				}

				// Experimental - Make sure that we don't have a relative image path
				if ($item->itemImage && substr($item->itemImage, 0, 4) !== "http" && substr($item->itemImage, 0, 1) !== "/") {
					$item->itemImage = JURI::root() . '' . $item->itemImage;
				}
			}

			// Title
			$item->itemTitle = $this->utilities->wordLimit($item->title, $detailBoxParams['detailBoxTitleLimit']);
			$item->itemTitleRaw = $item->title;
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxTitle']) {
				$item->hover_itemTitle = $this->utilities->wordLimit($item->title, $hoverBoxParams['hoverBoxTitleLimit']);
			}

			// Links
			$urls = json_decode($item->urls, true);

			if ($urls['title_url'])
				$item->itemLink = $urls['title_url'];
			if ($urls['category_url'])
				$item->itemCategoryLink = $urls['category_url'];
			if ($urls['author_url'])
				$item->itemAuthorLink = $urls['author_url'];

			// Introtext
			if ($detailBoxParams['detailBoxStripTags']) {
				$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				$item->itemIntrotext = preg_replace('/\[.*\]/', '', $item->itemIntrotext);
				$item->itemIntrotext = $this->utilities->wordLimit($item->itemIntrotext, $detailBoxParams['detailBoxIntrotextLimit']);
			} else {
				$item->itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
			}

			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxStripTags']) {
				$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
				$item->hover_itemIntrotext = preg_replace('/\[.*\]/', '', $item->hover_itemIntrotext);
				$item->hover_itemIntrotext = $this->utilities->wordLimit($item->hover_itemIntrotext, $hoverBoxParams['hoverBoxIntrotextLimit']);
			} else {
				$item->hover_itemIntrotext = preg_replace('/\{.*\}/', '', $item->description);
			}

			// Date
			$item->itemDate = JHTML::_('date', $item->created, $detailBoxParams['detailBoxDateFormat']);
			if ($hoverBoxParams['hoverBox'] && $hoverBoxParams['hoverBoxDate']) {
				$item->hover_itemDate = JHTML::_('date', $item->created, $hoverBoxParams['hoverBoxDateFormat']);
			}
			$item->itemDateRaw = $item->created;

			// Category
			if ($item->category != '') {
				$item->itemCategoryRaw = $item->category;
				$item->itemCategory = $item->itemCategoryRaw;
				if (isset($item->itemCategoryLink) && $item->itemCategoryLink) {
					$item->itemCategory = '<a href="' . $item->itemCategoryLink . '">' . $item->itemCategoryRaw . '</a>';
				}
			}

			// Author
			if ($item->author) {
				$item->itemAuthorRaw = $item->author;
			} else {
				$item->itemAuthorRaw = JFactory::getUser($item->created_by)->name;
			}
			$item->itemAuthor = $item->itemAuthorRaw;
			if (isset($item->itemAuthorLink) && $item->itemAuthorLink) {
				$item->itemAuthor = '<a href="' . $item->itemAuthorLink . '">' . $item->itemAuthorRaw . '</a>';
			}

			// Tags
			$item->itemTags = json_decode($item->tags, false);
			$tagsArray = array();
			foreach ($item->itemTags as $key => $tag) {
				if ($tag->title == '') {
					unset($item->itemTags->$key);
				} else {
					$tagsArray[] = $tag->title;
				}
			}

			if (!empty($tagsArray))
				$item->itemTagsLayout = implode(', ', $tagsArray);
		}

		return $items;
	}

	public function getWidgetDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams)
	{
		$sourceID = $this->utilities->getSourceID($widgetID);

		if ($sourceID == 'joomla') {
			return $this->getJoomlaDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'custom') {
			return $this->getCustomDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'k2') {
			return $this->getK2DisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'jomsocial') {
			return $this->getJomsocialDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'virtuemart') {
			return $this->getVirtuemartDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'easyblog') {
			return $this->getEasyblogDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'easysocial') {
			return $this->getEasysocialDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'folder') {
			return $this->getFolderDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		} else if ($sourceID == 'rss') {
			return $this->getRSSDisplayOptions($widgetID, $items, $detailBoxParams, $hoverBoxParams);
		}
	}
}
