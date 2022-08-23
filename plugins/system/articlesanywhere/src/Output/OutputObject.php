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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data\Numbers;

class OutputObject
{
	var $config;
	var $item;
	var $numbers;
	var $values;

	public function __construct(Config $config, Item $item, Numbers $numbers)
	{
		$this->config  = $config;
		$this->item    = $item;
		$this->numbers = $numbers;
		$this->values  = new Values($config, $item, $numbers);
	}
}
