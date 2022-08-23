<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.filesystem.folder');

class MinitekWallLibUtilities
{
	public static function getParams($option)
	{
		$application = JFactory::getApplication();

		if ($application->isSite())
		{
		  $params = $application->getParams($option);
		}
		else
		{
		  $params = JComponentHelper::getParams($option);
		}

		return $params;
	}

	// Get source id
  public static function getSourceID($widgetID)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__minitek_wall_widgets') . ' '
			. ' WHERE '.$db->quoteName('id').' = ' . $db->Quote($widgetID);
		$db->setQuery($query);
		$source_id = $db->loadObject()->source_id;

		return $source_id;
  }

	// Get source
  public static function getSource($widgetID, $source_id)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__minitek_wall_widgets_source') . ' '
			. ' WHERE '.$db->quoteName('widget_id').' = ' . $db->Quote($widgetID);
		$db->setQuery($query);
		$source_id = $source_id.'_source';
		$data_source = $db->loadObject()->$source_id;

		return self::decodeJSONParams($data_source);
  }

	// Decode json params
  public static function decodeJSONParams($json)
	{
		$params = json_decode($json, true);

		return $params;
  }

	// Get masonry_params
  public static function getMasonryParams($widgetID)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__minitek_wall_widgets')
			. ' WHERE '. $db->quoteName('state').' = '. $db->Quote('1')
			. ' AND '. $db->quoteName('id').' = '. $db->Quote($widgetID);

		$db->setQuery($query);
		$result = $db->loadObject();
		$masonry_params = $result->masonry_params;

		return self::decodeJSONParams($masonry_params);
  }

	// Get scroller_params
  public static function getScrollerParams($widgetID)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__minitek_wall_widgets')
			. ' WHERE '. $db->quoteName('state').' = '. $db->Quote('1')
			. ' AND '. $db->quoteName('id').' = '. $db->Quote($widgetID);

		$db->setQuery($query);
		$result = $db->loadObject();
		$scroller_params = $result->scroller_params;

		return self::decodeJSONParams($scroller_params);
  }

	public static function cleanName($name)
	{
		$name_fixed = preg_replace('/(?=\P{Nd})\P{L}/u', '-', $name);
		$name_fixed = preg_replace('/[\s-]{2,}/u', '-', $name_fixed);
		$name_fixed = htmlspecialchars($name_fixed);
		$name_fixed = trim($name_fixed, "-");

		return $name_fixed;
	}

	public static function recurseMasItemIndex($item_index, $gridType)
	{
		$item_index = $item_index - $gridType;

		if ($item_index > $gridType)
		{
			$item_index = self::recurseMasItemIndex($item_index, $gridType);
		}

		return $item_index;
	}

	public static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
	{
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
		$rgbArray = array();

		if (strlen($hexStr) == 6)
		{
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		}
		elseif (strlen($hexStr) == 3)
		{
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		}
		else
		{
			return false;
		}

		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
	}

	public static function wordLimit($str, $limit = 100, $end_char = '&#8230;')
	{
		if (JString::trim($str) == '')
			return $str;

		$str = strip_tags($str);
		$find = array("/\r|\n/u", "/\t/u", "/\s\s+/u");
		$replace = array(" ", " ", " ");
		$str = preg_replace($find, $replace, $str);
		$str = preg_replace("/\{\w+\}/", "", $str);
		preg_match('/\s*(?:\S*\s*){'.(int)$limit.'}/u', $str, $matches);

		if (JString::strlen($matches[0]) == JString::strlen($str))
			$end_char = '';

		return JString::rtrim($matches[0]).$end_char;
	}

	public static function makeDir( $path )
	{
		$folders = explode ('/',  ($path));
		$tmppath = JPATH_SITE.DS.'images'.DS.'mnwallimages'.DS;
		if (!file_exists($tmppath))
		{
			JFolder::create($tmppath, 0755);
		}

		for ($i = 0; $i < count ($folders) - 1; $i++)
		{
			if (!file_exists($tmppath.$folders[$i]) && !JFolder::create($tmppath.$folders[$i], 0755))
			{
				return false;
			}

			$tmppath = $tmppath.$folders[$i].DS;
		}

		return true;
	}

	public static function renderImages($path, $width, $height, $title='', $type='')
	{
		$params = self::getParams('com_minitekwall');

	  // PHP Thumb
		if ($params->get('load_phpthumb', true))
		{
			if (!defined('PhpThumbFactoryLoaded'))
			{
			  require_once(JPATH_SITE.DS.'components'.DS.'com_minitekwall'.DS.'libraries'.DS.'utilities'.DS.'phpthumb'.DS.'ThumbLib.inc.php');
				define('PhpThumbFactoryLoaded', 1);
			}
		}

		// Check if path starts with // (fix for easyblog images)
		if (substr($path, 0, 2) === '//')
		{
			if (substr(JURI::base(), 0, 5) === 'https')
			{
				$path = 'https:'.$path;
			}
			else
			{
				$path = 'http:'.$path;
			}
		}

	  $path = str_replace(JURI::base(), '', $path);
		$imgSource = JPATH_SITE.DS.str_replace('/', DS, $path);

		if (file_exists($imgSource))
		{
			if ($type)
			{
				$path =  $width."x".$height.'x'.$type.'/'.$path;
			}
			else
			{
				$path =  $width."x".$height.'/'.$path;
			}

			$thumbPath = JPATH_SITE.DS.'images'.DS.'mnwallimages'.DS.str_replace('/', DS, $path);

			if (!file_exists($thumbPath))
			{
			  $thumb = PhpThumbFactory::create($imgSource);

				if (!self::makeDir($path))
				{
					return '';
				}

				$thumb->adaptiveResize($width, $height);
				$thumb->save($thumbPath);
			}

			$path = JURI::base().'images/mnwallimages/'.$path;
		}

		return $path;
	}

	// Convert category ids to category names
	public static function getCategoriesNames($sourcetype_key, $cat_ids)
	{
		$cat_names = array();

		switch ($sourcetype_key)
		{
			// Joomla
			case 'joomla_mode':
				$categories = JCategories::getInstance('Content');

				foreach ($cat_ids as $catid)
				{
					$category = $categories->get($catid);
					$cat_names[] = $category->title;
				}
				break;

			// K2
			case 'k2_mode':
				foreach ($cat_ids as $catid)
				{
					$cat_names[] = self::getK2CategoryName($catid);
				}
				break;

			// Easyblog
			case 'eb_title':
				foreach ($cat_ids as $catid)
				{
					$cat_names[] = self::getEasyblogCategoryName($catid);
				}
				break;

			// Virtuemart
			case 'vmp_title':
				foreach ($cat_ids as $catid)
				{
					$cat_names[] = self::getVirtuemartCategoryName($catid);
				}
				break;
		}

		return $cat_names;
	}

	// Convert tag ids to tag names
	public static function getTagsNames($sourcetype_key, $tag_ids)
	{
		$tag_names = array();

		switch ($sourcetype_key)
		{
			// Joomla
			case 'joomla_mode':
				$tagsHelper = new JHelperTags;
				$all_tags = $tagsHelper->getTagNames($tag_ids);

				foreach ($all_tags as $key => $itemTag)
				{
					$tag_names[] = $itemTag;
				}
				break;

			// K2
			case 'k2_mode':
				foreach ($tag_ids as $key => $tagId)
				{
					$tag_names[] = self::getK2TagName($tagId);
				}
				break;

			// Easyblog
			case 'eb_title':
				foreach ($tag_ids as $key => $tagId)
				{
					$tag_names[] = self::getEasyblogTagName($tagId);
				}
				break;

			// Virtuemart
			case 'vmp_title':
				foreach ($tag_ids as $key => $tagId)
				{
					$tag_names[] = self::getVirtuemartManufacturerName($tagId);
				}
				break;
		}

		return $tag_names;
	}

	// Get K2 category name
  public static function getK2CategoryName($catId)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__k2_categories')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($catId)
			. ' AND '. $db->quoteName('published').' = '. $db->Quote('1');

		$db->setQuery($query);
		$result = $db->loadObject();
		$catName = $result->name;

		return $catName;
  	}

	// Get K2 tag name
  public static function getK2TagName($tagId)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__k2_tags')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($tagId)
			. ' AND '. $db->quoteName('published').' = '. $db->Quote('1');

		$db->setQuery($query);
		$result = $db->loadObject();
		$tagName = $result->name;

		return $tagName;
  	}

	// Get Easyblog category name
  public static function getEasyblogCategoryName($catId)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__easyblog_category')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($catId)
			. ' AND '. $db->quoteName('published').' = '. $db->Quote('1');

		$db->setQuery($query);
		$result = $db->loadObject();

		$catName = $result->title;

		return $catName;
  	}

	// Get Easyblog tag name
  public static function getEasyblogTagName($tagId)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__easyblog_tag')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($tagId)
			. ' AND '. $db->quoteName('published').' = '. $db->Quote('1');

		$db->setQuery($query);
		$result = $db->loadObject();
		$tagName = $result->title;

		return $tagName;
  	}

	// Get Virtuemart category name
  public static function getVirtuemartCategoryName($catId)
	{
		$categoryModel = VmModel::getModel('Category');
		$category = $categoryModel->getCategory((int)$catId);
		$categoryName = $category->category_name;

		return $categoryName;
	}

	// Get Virtuemart manufacturer name
  public static function getVirtuemartManufacturerName($tagId)
	{
		$manufacturerModel = VmModel::getModel('Manufacturer');
		$manufacturer = $manufacturerModel->getManufacturer((int)$tagId);
		$manufacturerName = $manufacturer->mf_name;

		return $manufacturerName;
	}

	// Get Joomla parent categories
  public static function getJoomlaParentCats($catid, $maxlevel, $level = 0, $cats = array())
	{
		$categories = JCategories::getInstance('Content');
		$cat = $categories->get($catid);
		if ($cat->getParent() && $cat->getParent()->id != 'root')
		{
			$parent = $cat->getParent();
			$level++;
			if ($level <= $maxlevel)
			{
				array_push($cats, array("id"=>$parent->id, "category_name"=>$parent->title));
				$cats = self::getJoomlaParentCats($parent->id, $maxlevel, $level, $cats);
			}
		}

		return $cats;
	}

	// Get K2 category
  public static function getK2Category($catId)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__k2_categories')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($catId)
			. ' AND '. $db->quoteName('published').' = '. $db->Quote('1');

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
  	}

	// Get K2 parent categories
  public static function getK2ParentCats($catid, $maxlevel, $level = 0, $cats = array())
	{
		$cat = self::getK2Category($catid);
		if ($cat->parent)
		{
			$parent = $cat->parent;
			$level++;
			if ($level <= $maxlevel)
			{
				array_push($cats, array("id"=>$parent, "category_name"=>self::getK2Category($parent)->name));
				$cats = self::getK2ParentCats($parent, $maxlevel, $level, $cats);
			}
		}

		return $cats;
	}

	// Get ordering from data source
	public static function getItemsOrdering($data_source)
	{
		reset($data_source);
		$sourcetype_key = key($data_source); // get first key of array data source
		$ordering = 'title';

		switch ($sourcetype_key)
		{
			// Joomla
			case 'joomla_mode':
				if ($data_source['joomla_mode'] == 'ja')
				{
					$ordering = $data_source['ja_article_ordering'];
					if (($pos = strpos($ordering, '.')) !== FALSE) {
						if ($ordering == 'fp.ordering')
						{
							$ordering = 'fordering';
						}
						else
						{
							$ordering = substr($ordering, strrpos($ordering, '.') + 1);
						}
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
					if ($ordering == 'publish_up')
					{
						$ordering = 'start';
					}
					if ($ordering == 'publish_down')
					{
						$ordering = 'finish';
					}
				}
				else if ($data_source['joomla_mode'] == 'jc')
				{
					$ordering = $data_source['jc_ordering'];
					if ($ordering == 'alpha')
					{
						$ordering = 'title';
					}
				}
				break;

			// Custom Items
			case 'custom_title':
				$ordering = $data_source['custom_ordering'];
				if ($ordering == 'created')
				{
					$ordering = 'date';
				}
				if ($ordering == 'publish_up')
				{
					$ordering = 'start';
				}
				if ($ordering == 'publish_down')
				{
					$ordering = 'finish';
				}
				break;

			// K2
			case 'k2_mode':
				if ($data_source['k2_mode'] == 'k2i')
				{
					$ordering = $data_source['k2i_ordering'];
					if ($ordering == 'publishUp')
					{
						$ordering = 'start';
					}
					if ($ordering == 'order')
					{
						$ordering = 'ordering';
					}
					if ($ordering == 'best')
					{
						$ordering = 'rating';
					}
					if ($ordering == 'alpha')
					{
						$ordering = 'title';
					}
				}
				else if ($data_source['k2_mode'] == 'k2c')
				{
					$ordering = $data_source['k2c_ordering'];
					if ($ordering == 'alpha')
					{
						$ordering = 'title';
					}
					if ($ordering == 'order')
					{
						$ordering = 'ordering';
					}
				}
				else if ($data_source['k2_mode'] == 'k2a')
				{
					if (array_key_exists('k2a_ordering', $data_source))
					{
						$ordering = $data_source['k2a_ordering'];
						if ($ordering == 'userName')
						{
							$ordering = 'title';
						}
					}
				}
				break;

			// Easyblog
			case 'eb_title':
				$ordering = $data_source['eb_sortby'];
				if ($ordering == 'latest')
				{
					$ordering = 'date';
				}
				if ($ordering == 'published')
				{
					$ordering = 'start';
				}
				if ($ordering == 'popular')
				{
					$ordering = 'hits';
				}
				if ($ordering == 'alphabet')
				{
					$ordering = 'title';
				}
				break;

			// Easysocial
			case 'easysocial_mode':
				if ($data_source['easysocial_mode'] == 'esu')
				{
					$ordering = $data_source['esu_ordering'];
					if ($ordering == 'registerDate')
					{
						$ordering = 'date';
					}
					if ($ordering == 'id')
					{
						$ordering = 'id';
					}
					if ($ordering == 'name')
					{
						$ordering = 'title';
					}
				}
				else if ($data_source['easysocial_mode'] == 'esg')
				{
					$ordering = $data_source['esg_ordering'];
					if ($ordering == 'latest')
					{
						$ordering = 'date';
					}
					if ($ordering == 'title')
					{
						$ordering = 'title';
					}
					if ($ordering == 'popular')
					{
						$ordering = 'hits';
					}
					if ($ordering == 'members')
					{
						$ordering = 'members';
					}
				}
				else if ($data_source['easysocial_mode'] == 'ese')
				{
					$ordering = $data_source['ese_ordering'];
					if ($ordering == 'start')
					{
						$ordering = 'date';
					}
					if ($ordering == 'end')
					{
						$ordering = 'finish';
					}
					if ($ordering == 'name')
					{
						$ordering = 'title';
					}
				}
				else if ($data_source['easysocial_mode'] == 'esp')
				{
					$ordering = $data_source['esp_ordering'];
					if ($ordering == 'title')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
				}
				else if ($data_source['easysocial_mode'] == 'esa')
				{
					$ordering = $data_source['esa_ordering'];
					if ($ordering == 'title')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
					if ($ordering == 'hits')
					{
						$ordering = 'hits';
					}
				}
				else if ($data_source['easysocial_mode'] == 'esv')
				{
					$ordering = $data_source['esv_ordering'];
					if ($ordering == 'title')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
					if ($ordering == 'popular')
					{
						$ordering = 'hits';
					}
				}
				break;

			// Virtuemart
			case 'vmp_title':
				$ordering = $data_source['vmp_ordering'];
				if ($ordering == 'created_on')
				{
					$ordering = 'date';
				}
				if ($ordering == 'modified_on')
				{
					$ordering = 'modified';
				}
				if ($ordering == 'product_name')
				{
					$ordering = 'title';
				}
				if ($ordering == 'product_sales')
				{
					$ordering = 'sales';
				}
				break;

			// Jomsocial
			case 'jomsocial_mode':
				if ($data_source['jomsocial_mode'] == 'jsu')
				{
					$ordering = $data_source['jsu_ordering'];
					if ($ordering == 'name')
					{
						$ordering = 'title';
					}
					if ($ordering == 'registerDate')
					{
						$ordering = 'date';
					}
					if ($ordering == 'friendcount')
					{
						$ordering = 'friends';
					}
					if ($ordering == 'view')
					{
						$ordering = 'hits';
					}
				}
				else if ($data_source['jomsocial_mode'] == 'jsg')
				{
					$ordering = $data_source['jsg_ordering'];
					if ($ordering == 'name')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
					if ($ordering == 'membercount')
					{
						$ordering = 'members';
					}
				}
				else if ($data_source['jomsocial_mode'] == 'jse')
				{
					$ordering = $data_source['jse_ordering'];
					if ($ordering == 'startdate')
					{
						$ordering = 'date';
					}
					if ($ordering == 'enddate')
					{
						$ordering = 'finish';
					}
					if ($ordering == 'confirmedcount')
					{
						$ordering = 'confirmed';
					}
					if ($ordering == 'ticket')
					{
						$ordering = 'tickets';
					}
				}
				else if ($data_source['jomsocial_mode'] == 'jsp')
				{
					$ordering = $data_source['jsp_ordering'];
					if ($ordering == 'caption')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
				}
				else if ($data_source['jomsocial_mode'] == 'jsa')
				{
					$ordering = $data_source['jsa_ordering'];
					if ($ordering == 'name')
					{
						$ordering = 'title';
					}
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
				}
				else if ($data_source['jomsocial_mode'] == 'jsv')
				{
					$ordering = $data_source['jsv_ordering'];
					if ($ordering == 'created')
					{
						$ordering = 'date';
					}
				}
				break;

			// Folder
			case 'fold_title':
				$ordering = $data_source['fold_ordering'];
				if ($ordering == 'created')
				{
					$ordering = 'date';
				}
				break;

			// RSS
			case 'rss_title':
				$ordering = 'index';
				break;
		}

		return $ordering;
	}

	// Get ordering direction from data source
	public static function getItemsDirection($data_source)
	{
		reset($data_source);
		$sourcetype_key = key($data_source); // get first key of array data source
		$direction = 'DESC';

		switch ($sourcetype_key)
		{
			// Joomla
			case 'joomla_mode':
				if ($data_source['joomla_mode'] == 'ja')
				{
					$direction = $data_source['ja_article_ordering_direction'];
				}

				else if ($data_source['joomla_mode'] == 'jc')
				{
					if (array_key_exists('jc_ordering_direction', $data_source))
					{
						$direction = $data_source['jc_ordering_direction'];
					}
				}
				break;

			// Custom Items
			case 'custom_title':
				$direction = $data_source['custom_ordering_direction'];
				break;

			// K2
			case 'k2_mode':
				if ($data_source['k2_mode'] == 'k2i')
				{
					$direction = $data_source['k2i_ordering_direction'];
				}
				else if ($data_source['k2_mode'] == 'k2c')
				{
					$direction = $data_source['k2c_ordering_direction'];
				}
				else if ($data_source['k2_mode'] == 'k2a')
				{
					if (array_key_exists('k2a_ordering_direction', $data_source))
					{
						$ordering = $data_source['k2a_ordering_direction'];
					}
				}
				break;

			// Easyblog
			case 'eb_title':
				$direction = $data_source['eb_sortDirection'];
				break;

			// Easysocial
			case 'easysocial_mode':
				if ($data_source['easysocial_mode'] == 'esu')
				{
					$direction = $data_source['esu_ordering_direction'];
				}
				else if ($data_source['easysocial_mode'] == 'esg')
				{
					$direction = $data_source['esg_ordering_direction'];
				}
				else if ($data_source['easysocial_mode'] == 'ese')
				{
					$direction = $data_source['ese_ordering_direction'];
				}
				else if ($data_source['easysocial_mode'] == 'esp')
				{
					$direction = $data_source['esp_ordering_direction'];
				}
				else if ($data_source['easysocial_mode'] == 'esa')
				{
					$direction = $data_source['esa_ordering_direction'];
				}
				else if ($data_source['easysocial_mode'] == 'esv')
				{
					$direction = $data_source['esv_ordering_direction'];
				}
				break;

			// Virtuemart
			case 'vmp_title':
				$direction = $data_source['vmp_ordering_direction'];
				break;

			// Jomsocial
			case 'jomsocial_mode':
				if ($data_source['jomsocial_mode'] == 'jsu')
				{
					$direction = $data_source['jsu_ordering_direction'];
				}
				else if ($data_source['jomsocial_mode'] == 'jsg')
				{
					$direction = $data_source['jsg_ordering_direction'];
				}
				else if ($data_source['jomsocial_mode'] == 'jse')
				{
					$direction = $data_source['jse_ordering_direction'];
				}
				else if ($data_source['jomsocial_mode'] == 'jsp')
				{
					$direction = $data_source['jsp_ordering_direction'];
				}
				else if ($data_source['jomsocial_mode'] == 'jsa')
				{
					$direction = $data_source['jsa_ordering_direction'];
				}
				else if ($data_source['jomsocial_mode'] == 'jsv')
				{
					$direction = $data_source['jsv_ordering_direction'];
				}
				break;

			// Folder
			case 'fold_title':
				$direction = $data_source['fold_ordering_direction'];
				break;

			// RSS
			case 'rss_title':
				$direction = 'ASC';
				break;
		}

		return $direction;
	}

	// Get custom grid
  public static function getCustomGrid($id)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT * '
			. ' FROM '. $db->quoteName('#__minitek_wall_grids')
			. ' WHERE '. $db->quoteName('id').' = '. $db->Quote($id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
  }
}
