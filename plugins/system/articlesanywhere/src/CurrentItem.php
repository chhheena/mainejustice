<?php
/**
 * @package         Articles Anywhere
 * @version         12.4.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;

class CurrentItem
{
	static $item;
	var    $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public static function exists()
	{
		return ! is_null(self::$item);
	}

	public static function get($key = null)
	{
		$item = self::getCurrentItem();

		if (is_null($key))
		{
			return $item ?: (object) [];
		}

		return $item->{$key} ?? null;
	}

	public static function set($item)
	{
		if ( ! isset($item->id))
		{
			return;
		}

		self::$item = $item;
	}

	private static function getCurrentItem()
	{
		if ( ! is_null(self::$item))
		{
			return self::$item;
		}

		$input = JFactory::getApplication()->input;

		if ($input->get('option') != 'com_content' || $input->get('view') != 'article')
		{
			return null;
		}

		if ( ! $id = $input->get('id'))
		{
			return null;
		}

		if ( ! class_exists('ContentModelArticle'))
		{
			require_once JPATH_SITE . '/components/com_content/models/article.php';
		}

		$model = JModel::getInstance('article', 'contentModel');

		if ( ! method_exists($model, 'getItem'))
		{
			return null;
		}

		$item = $model->getItem($id);

		if (empty($item->id))
		{
			return null;
		}

		self::$item = $item;

		return self::$item;
	}
}
