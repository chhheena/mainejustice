<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ContentTemplater;

defined('_JEXEC') or die;

use ContentTemplaterModelItem;
use ContentTemplaterModelList;
use Joomla\CMS\Factory as JFactory;

class Items
{
	static $default_item = null;
	static $items        = [];

	public static function get($type = '')
	{
		if (isset(self::$items[$type]))
		{
			return self::$items[$type];
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_contenttemplater/models/list.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_contenttemplater/models/item.php';

		$list = new ContentTemplaterModelList;
		$list->setState('limit', 0);
		$list->setState('limitstart', 0);
		$items = $list->getItems(true, 'a.ordering');

		$item_model = new ContentTemplaterModelItem;

		self::$items[$type] = [];

		foreach ($items as $item)
		{
			// not enabled if: not published
			if ( ! $item->published)
			{
				continue;
			}

			$item = $item_model->getItem($item->id, false, false, true);

			if ( ! Conditions::itemPass($item, $type))
			{
				continue;
			}

			self::$items[$type][] = $item;
		}

		return self::$items[$type];
	}

	public static function getDefaultItem()
	{
	}

	private static function getFirstDefaultItem()
	{
	}
}
