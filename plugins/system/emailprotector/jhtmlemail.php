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

defined('JPATH_PLATFORM') or die;

abstract class JHtmlEmail
{
	public static function cloak($mail, $mailto = true, $text = '', $email = true)
	{
		if ($mailto)
		{
			if ( ! $text)
			{
				$text = $mail;
			}
			$mail = '<a href="mailto:' . $mail . '">' . $text . '</a>';
		}

		return $mail;
	}
}
