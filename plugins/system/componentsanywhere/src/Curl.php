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

class Curl
{
	public static function get($url, $cookies = [])
	{
		if ( ! function_exists('curl_init'))
		{
			return false;
		}

		$params = Params::get();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $params->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params->timeout);

		// Adding a valid user agent string, otherwise some feed-servers returning an error
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if ( ! empty($cookies))
		{
			$session = session_name() . '=' . session_id();

			// Update current session in cookies because of not being able to overwrite it
			if (($key = array_search($session, $cookies)) !== false)
			{
				unset($cookies[$key]);
				$cookies[] = session_name() . '=' . md5(session_id() . time());
			}

			curl_setopt($ch, CURLOPT_COOKIESESSION, false); // False to keep all cookies of previous session
			curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
		}

		if ( ! empty($_POST))
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
		}

		self::setCurlAuthentication($ch);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3); // stop after 3 redirects
		$html  = curl_exec($ch);
		$error = curl_error($ch);

		curl_close($ch);

		if (JFactory::getApplication()->input->get('debug_component'))
		{
			echo "\n\n<pre>=======Curl url===================\n";
			print_r($url);
			echo "\n=============Error=============\n";
			print_r($error);
			echo "\n==============HTML============\n";
			print_r($html);
			echo "\n==========================</pre>\n\n";
		}

		if (empty($html) && ! empty($error))
		{
			return Component::createJsonString(
				Protect::getMessageCommentTag('CURL Error: ' . $error)
			);
		}

		return $html;
	}

	private static function setCurlAuthentication(&$ch)
	{
		$params = Params::get();

		if ($params->use_negotiate_authentication)
		{
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
			curl_setopt($ch, CURLOPT_USERPWD, $params->negotiate_login . ':' . $params->negotiate_password);

			return;
		}

		if ( ! $credentials = Data::getAuthenticationCredentials())
		{
			return;
		}

		[$username, $password] = $credentials;

		// Send authentication
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password); // set referer on redirect
	}
}
