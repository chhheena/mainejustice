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

class Extra extends Data
{
	var $groups = ['attribs', 'urls', 'images', 'metadata'];

	public function get($key, $attributes)
	{
		foreach ($this->groups as $group)
		{
			$value = $this->item->getFromGroup($group, $key);

			if (is_null($value))
			{
				continue;
			}

			return $value;
		}

		return null;
	}

}
