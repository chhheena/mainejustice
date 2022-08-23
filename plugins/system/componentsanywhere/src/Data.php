<?php
/**
 * @package         Components Anywhere
 * @version         4.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ComponentsAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;

class Data
{
	static $credentials = null;

	public static function get($url)
	{
		$params = Params::get();

		if (JFactory::getApplication()->input->get('debug_component'))
		{
			echo "\n\n<pre>========Data==================\n";
			print_r(self::getUrl($url));
			echo "\n==========================</pre>\n\n";
		}

		$cookies = self::getCookies();

		if ($params->force_curl)
		{
			return Curl::get(self::getUrl($url), $cookies);
		}

		$data = Http::get(self::getUrl($url, $cookies));

		if ( ! empty($data))
		{
			return $data;
		}

		// Fall back on Curl if html is empty or not a json string
		return Curl::get(self::getUrl($url), $cookies);
	}

	public static function getAuthenticationCredentials()
	{
		if ( ! is_null(self::$credentials))
		{
			return self::$credentials;
		}

		if (isset($_SERVER['PHP_AUTH_USER']))
		{
			self::$credentials = [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ?? ''];

			return self::$credentials;
		}

		if (isset($_SERVER['HTTP_AUTHENTICATION'])
			&& strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']), 'basic') === 0
		)
		{
			self::$credentials = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

			return self::$credentials;
		}

		return false;
	}

	private static function getCookies()
	{
		$params = Params::get();

		if ( ! $params->pass_on_cookies)
		{
			return [];
		}

		$cookies = [];

		foreach ($_COOKIE as $k => $v)
		{
			// Only include hexadecimal keys
			// Why was this here?
//			if ( ! RL_RegEx::match('^[a-f0-9]+$', $k))
//			{
//				continue;
//			}

			$cookies[] = $k . '=' . $v;
		}

		return $cookies;
	}

	private static function getUrl($url, $cookies = [])
	{
		$params = Params::get();

		// Add the language to make sure the correct Itemid is found during routing
		$url .= (strpos($url, '?') === false ? '?' : '&') . 'lang=' . JFactory::getLanguage()->getTag();

		// Add cookies to the url
		if ( ! empty($cookies))
		{
			$url .= '&' . implode('&', $cookies);
		}

		// Pass url through the JRoute if it is a non-SEF url
		if (strpos($url, 'index.php?') !== false)
		{
			$url = JRoute::_($url, false);
		}

		// Add the Components Anywhere stuff to the sef/non-sef url
		$url .= (strpos($url, '?') === false ? '?' : '&') . 'tmpl=' . $params->tmpl . '&rendercomponent=1';

		return JUri::getInstance()->toString(['scheme', 'user', 'pass', 'host', 'port']) . '/' . ltrim($url, '/');
	}
}
