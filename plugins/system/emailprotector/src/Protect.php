<?php
/**
 * @package         Email Protector
 * @version         4.7.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\EmailProtector;

defined('_JEXEC') or die;

use RegularLabs\Library\Protect as RL_Protect;

class Protect
{
	static $name = 'Email Protector';

	public static function _(&$string)
	{
		RL_Protect::protectFields($string);
		RL_Protect::protectScripts($string);
		RL_Protect::protectSourcerer($string);
	}

	public static function protectHtmlCommentTags(&$string)
	{
		RL_Protect::protectHtmlCommentTags($string, self::$name);
	}

	public static function protectHtmlTags(&$string)
	{
		RL_Protect::protectHtmlTags($string);
	}

	public static function removeInlineComments(&$string)
	{
		RL_Protect::removeInlineComments($string, self::$name);
	}

	public static function unprotect(&$string)
	{
		RL_Protect::unprotect($string);
	}
}
