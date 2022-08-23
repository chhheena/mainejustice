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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Values;

class Data implements DataInterface
{
	static $static_item;
	var    $config;
	var    $item;
	var    $values;

	public function __construct(Config $config, Item $item, Values $values)
	{
		$this->config      = $config;
		$this->item        = $item;
		$this->values      = $values;
		self::$static_item = $item;
	}

	public function get($key, $attributes)
	{
		return null;
	}
}
