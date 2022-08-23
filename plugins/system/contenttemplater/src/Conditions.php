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

use RegularLabs\Library\Conditions as RL_Conditions;
use RegularLabs\Library\Document as RL_Document;

class Conditions
{
	public static function itemPass($item, $type = '')
	{
		if ($type && ! $item->{$type . '_enabled'})
		{
			return false;
		}

		// not enabled if: not active in this area (frontend/backend)
		if ($type &&
			(
				(RL_Document::isClient('administrator') && $item->{$type . '_enable_in_frontend'} == 2)
				|| (RL_Document::isClient('site') && $item->{$type . '_enable_in_frontend'} == 0)
			)
		)
		{
			return false;
		}



		return true;
	}
}
/* <<< [PRO] <<< */
