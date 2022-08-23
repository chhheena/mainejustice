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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Components\K2;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;

class CurrentItem
{
	static $item;
	var    $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function get()
	{
		if ( ! is_null(self::$item))
		{
			return self::$item;
		}

		$input = JFactory::getApplication()->input;

		if ($input->get('option') != 'com_k2' || $input->get('view') != 'item')
		{
			return null;
		}

		if ( ! $id = $input->get('id'))
		{
			return null;
		}

		if ( ! class_exists('K2ModelItem'))
		{
			require_once JPATH_SITE . '/components/com_k2/models/item.php';
		}

		$model = JModel::getInstance('item', 'K2Model');

		if ( ! method_exists($model, 'getData'))
		{
			return null;
		}

		$item = $model->getData();

		if (empty($item->id))
		{
			return null;
		}

		self::$item = $item;

		return self::$item;
	}
}
