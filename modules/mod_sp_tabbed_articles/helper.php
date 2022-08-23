<?php
/*------------------------------------------------------------------------
# mod_sp_tabbed_articles - Tabbed articles module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2015 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
defined ('_JEXEC') or die('resticted aceess');

if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php');

class modSpTabbedArticlesHelper
{
	public static function getArticles( $count = 5, $ordering = 'latest', $catid = '', $include_subcategories = true, $post_format = '' ) {

		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
	
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
		->select('a.*')
		->from($db->quoteName('#__content', 'a'))
		->select($db->quoteName('b.alias', 'category_alias'))
		->select($db->quoteName('b.title', 'category'))
		->join('LEFT', $db->quoteName('#__categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')')
		->where($db->quoteName('b.extension') . ' = ' . $db->quote('com_content'));
		
		if($post_format) {
			$query->where($db->quoteName('a.attribs') . ' LIKE ' . $db->quote('%"post_format":"'. $post_format .'"%'));
		}
		
		$query->where($db->quoteName('a.state') . ' = ' . $db->quote(1));
		
		// Category filter
		if ($catid != '') {
			$categories = self::getCategories( $catid, $include_subcategories );
			array_unshift($categories, $catid);

			$query->where($db->quoteName('a.catid')." IN (" . implode( ',', $categories ) . ")");
		}
		
		// has order by
		if ($ordering == 'hits') {
			$query->order($db->quoteName('a.hits') . ' DESC');
		}elseif($ordering == 'featured'){
			$query->where($db->quoteName('a.featured') . ' = ' . $db->quote(1));
			$query->order($db->quoteName('a.created') . ' DESC');
		}else{
			$query->order($db->quoteName('a.created') . ' DESC');
		}

		// Language filter
		if ($app->getLanguageFilter()) {
			$query->where('a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}

		// continue query
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")");
		$query->order($db->quoteName('a.created') . ' DESC')
		->setLimit($count);

		$db->setQuery($query);
		$items = $db->loadObjectList();



		foreach ($items as &$item) {
			$item->slug    	= $item->id . ':' . $item->alias;
			$item->catslug 	= $item->catid . ':' . $item->category_alias;
			$item->username = JFactory::getUser($item->created_by)->name;
			$item->link 	= JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			$attribs 		= json_decode($item->attribs);

			// Featured Image
			if( (isset($attribs->spfeatured_image) && $attribs->spfeatured_image != NULL ) || ( isset($item->images) && (!empty($item->images)) ) ) {
				
				$featured_image = (isset($attribs->spfeatured_image) && $attribs->spfeatured_image) ? $attribs->spfeatured_image : $attribs->helix_ultimate_image ;

				$img_baseurl = basename($featured_image);
				$item->image_small = '';
				//Small
				$small = JPATH_ROOT . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) .  '_small.' . JFile::getExt($img_baseurl);
				if(file_exists($small)) {
					$item->image_small = JURI::root(true) . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) . '_small.' . JFile::getExt($img_baseurl);
				} elseif ($item->images) {
					$images = json_decode($item->images);
					if(isset($images->image_intro) && $images->image_intro) {
						$item->image_small = $images->image_intro;
					} elseif (isset($images->image_fulltext) && $images->image_fulltext) {
						$item->image_small = $images->image_fulltext;
					} else {
						$item->image_small = false;
					}
				}

				//Thumb
				$thumbnail = JPATH_ROOT . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) .  '_thumbnail.' . JFile::getExt($img_baseurl);
				if(file_exists($thumbnail)) {
					$item->image_thumbnail = JURI::root(true) . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) . '_thumbnail.' . JFile::getExt($img_baseurl);
				} else {
					$images = json_decode($item->images);
					if(isset($images->image_intro) && $images->image_intro) {
						$item->image_thumbnail = $images->image_intro;
					} elseif (isset($images->image_fulltext) && $images->image_fulltext) {
						$item->image_thumbnail = $images->image_fulltext;
					} else {
						$item->image_thumbnail = false;
					}
				}

				//Medium
				$medium = JPATH_ROOT . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) .  '_medium.' . JFile::getExt($img_baseurl);
				if(file_exists($medium)) {
					$item->image_medium = JURI::root(true) . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) . '_medium.' . JFile::getExt($img_baseurl);
				} else {
					$images = json_decode($item->images);
					if(isset($images->image_intro) && $images->image_intro) {
						$item->image_medium = $images->image_intro;
					} elseif (isset($images->image_fulltext) && $images->image_fulltext) {
						$item->image_medium = $images->image_fulltext;
					} else {
						$item->image_medium = false;
					}
				}

				//Large
				$large = JPATH_ROOT . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) .  '_large.' . JFile::getExt($img_baseurl);
				if(file_exists($large)) {
					$item->image_large = JURI::root(true) . '/' . dirname($featured_image) . '/' . JFile::stripExt($img_baseurl) . '_large.' . JFile::getExt($img_baseurl);
				} else {
					$images = json_decode($item->images);
					if (isset($images->image_fulltext) && $images->image_fulltext) {
						$item->image_thumbnail = $images->image_fulltext;
					} elseif(isset($images->image_intro) && $images->image_intro) {
						$item->image_thumbnail = $images->image_intro;
					} else {
						$item->image_thumbnail = false;
					}
				}
			}

			// Post Format
			$item->post_format = 'standard';
			if(isset($attribs->post_format) && $attribs->post_format != '') {
				$item->post_format = $attribs->post_format;
			}

			// Post Format Video
			if(isset($attribs->post_format) && $attribs->post_format == 'video') {
				if(isset($attribs->video) && $attribs->video != NULL) {
					$video = parse_url($attribs->video);

					$video_src = '';

					switch($video['host']) {
						case 'youtu.be':
						$video_id 	= trim($video['path'],'/');
						$video_src 	= '//www.youtube.com/embed/' . $video_id;
						break;

						case 'www.youtube.com':
						case 'youtube.com':
						parse_str($video['query'], $query);
						$video_id 	= $query['v'];
						$video_src 	= '//www.youtube.com/embed/' . $video_id;
						break;

						case 'vimeo.com':
						case 'www.vimeo.com':
						$video_id 	= trim($video['path'],'/');
						$video_src 	= "//player.vimeo.com/video/" . $video_id;
					}

					$item->video_src = $video_src;
				} else {
					$item->video_src = '';
				}
			}

			// Post Format Audio
			if(isset($attribs->post_format) && $attribs->post_format == 'audio') {
				if(isset($attribs->audio) && $attribs->audio != NULL) {
					$item->audio_embed = $attribs->audio;
				} else {
					$item->audio_embed = '';
				}
			}

			// Post Format Quote
			if(isset($attribs->post_format) && $attribs->post_format == 'quote') {
				if(isset($attribs->quote_text) && $attribs->quote_text != NULL) {
					$item->quote_text = $attribs->quote_text;
				} else {
					$item->quote_text = '';
				}

				if(isset($attribs->quote_author) && $attribs->quote_author != NULL) {
					$item->quote_author = $attribs->quote_author;
				} else {
					$item->quote_author = '';
				}
			}

			// Post Format Status
			if(isset($attribs->post_format) && $attribs->post_format == 'status') {
				if(isset($attribs->post_status) && $attribs->post_status != NULL) {
					$item->post_status = $attribs->post_status;
				} else {
					$item->post_status = '';
				}
			}

			// Post Format Link
			if(isset($attribs->post_format) && $attribs->post_format == 'link') {
				if(isset($attribs->link_title) && $attribs->link_title != NULL) {
					$item->link_title = $attribs->link_title;
				} else {
					$item->link_title = '';
				}

				if(isset($attribs->link_url) && $attribs->link_url != NULL) {
					$item->link_url = $attribs->link_url;
				} else {
					$item->link_url = '';
				}
			}

			// Post Format Gallery
			if(isset($attribs->post_format) && $attribs->post_format == 'gallery') {

				$item->imagegallery = new stdClass();

				if(isset($attribs->gallery) && $attribs->gallery != NULL) {
					$gallery_all_images = json_decode($attribs->gallery)->gallery_images;

					$gallery_images = array();

					foreach ($gallery_all_images as $key=>$value) {
						$gallery_images[$key]['full'] = $value;

						$gallery_img_baseurl = basename($value);

						//Small
						$small = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) .  '_small.' . JFile::getExt($gallery_img_baseurl);
						if(file_exists($small)) {
							$gallery_images[$key]['small'] = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) . '_small.' . JFile::getExt($gallery_img_baseurl);
						}

						//Thumbnail
						$thumbnail = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) .  '_thumbnail.' . JFile::getExt($gallery_img_baseurl);
						if(file_exists($thumbnail)) {
							$gallery_images[$key]['thumbnail'] = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) . '_thumbnail.' . JFile::getExt($gallery_img_baseurl);
						}

						//Medium
						$medium = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) .  '_medium.' . JFile::getExt($gallery_img_baseurl);
						if(file_exists($medium)) {
							$gallery_images[$key]['medium'] = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) . '_medium.' . JFile::getExt($gallery_img_baseurl);
						}

						//Large
						$large = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) .  '_large.' . JFile::getExt($gallery_img_baseurl);
						if(file_exists($large)) {
							$gallery_images[$key]['large'] = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($gallery_img_baseurl) . '_large.' . JFile::getExt($gallery_img_baseurl);
						}
					}

					$item->imagegallery->images = $gallery_images;

				} else {
					$item->imagegallery->images = array();
				}
			}
		}

		return $items;
	}

	public static function getCategories($parent_id = 1, $include_subcategories = true, $child = false) {
		if(!$child) {
			$cats = array();
		}
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
			->where($db->quoteName('language')." IN (" . $db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*') . ")")
			->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id))
			->order($db->quoteName('lft') . ' ASC');

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			array_push($cats, $row->id);
			if($include_subcategories) {
				if (self::hasChildren($row->id)) {
					self::getCategories($row->id, $include_subcategories, true);
				}
			}
		}

		return $cats;
	}

	public static function getSubcategories($parent_id = 1, $include_subcategories = true, $child = false) {
		if(!$child) {
			$subcats = array();
		}

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
			->where($db->quoteName('language')." IN (" . $db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*') . ")")
			->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id))
			->order($db->quoteName('lft') . ' ASC');

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			array_push($subcats, $row);
			if($include_subcategories) {
				if (self::hasChildren($row->id)) {
					self::getSubcategories($row->id, $include_subcategories, true);
				}
			}
		}

		return $subcats;
	}

	private static function hasChildren($parent_id = 1) {
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
			->where($db->quoteName('language')." IN (" . $db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*') . ")")
			->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id))
			->order($db->quoteName('created_time') . ' DESC');

		$db->setQuery($query);

		$childrens = $db->loadObjectList();

		if(count($childrens)) {
			return true;
		}

		return false;
	}
}
