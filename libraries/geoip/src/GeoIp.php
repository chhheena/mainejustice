<?php
/**
 * @package         GeoIp
 * @version         5.1.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\GeoIp;

defined('_JEXEC') or die;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Model\City;

require_once dirname(__FILE__, 2) . '/autoload.php';

class GeoIp
{
	var $data    = [];
	var $ip      = '';
	var $reader  = null;
	var $records = [];

	public function __construct($ip = '')
	{
		$this->ip            = $ip ?: $this->getIP();
		$this->database_file = self::getDatabasePath();

		if (in_array($this->ip, ['127.0.0.1', '::1']))
		{
			$this->ip = '';
		}
	}

	private function getIP()
	{
		if ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $this->isValidIP($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ( ! empty($_SERVER['HTTP_X_REAL_IP']) && $this->isValidIP($_SERVER['HTTP_X_REAL_IP']))
		{
			return $_SERVER['HTTP_X_REAL_IP'];
		}

		if ( ! empty($_SERVER['HTTP_CLIENT_IP']) && $this->isValidIP($_SERVER['HTTP_CLIENT_IP']))
		{
			$_SERVER['HTTP_CLIENT_IP'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	public static function getDatabasePath()
	{
		return JPATH_LIBRARIES . '/geoip/GeoLite2-City.mmdb';
	}

	private function isValidIP($string)
	{
		return filter_var(
			$string,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4
			//	| FILTER_FLAG_IPV6
			| FILTER_FLAG_NO_PRIV_RANGE
			| FILTER_FLAG_NO_RES_RANGE
		);
	}

	public function get($fields = null, $force_new = false)
	{
		if ( ! $fields = $this->initFields($fields))
		{
			return false;
		}

		$record = $this->getRecordData();

		if (empty($record))
		{
			return false;
		}

		$data = [];

		foreach ($fields as $field)
		{
			if ( ! $force_new && isset($this->data[$field]))
			{
				$data[$field] = $this->data[$field];
				continue;
			}

			switch ($field)
			{
				case'continentCode':
					$data[$field] = $record->continent->code;
					break;
				case 'continent':
					$data[$field] = $record->continent->name;
					break;
				case 'countryCode':
					$data[$field] = $record->country->isoCode;
					break;
				case 'country':
					$data[$field] = $this->getCountryName($record);
					break;
				case 'regionCode':
					$data[$field] = $record->mostSpecificSubdivision->isoCode;
					break;
				case 'region':
					$data[$field] = $record->mostSpecificSubdivision->name;
					break;
				case 'regionCodes':
					$data[$field] = [];
					foreach ($record->subdivisions as $region)
					{
						$data[$field][] = $region->isoCode;
					}
					break;
				case 'regions':
					$data[$field] = [];
					foreach ($record->subdivisions as $region)
					{
						$data[$field][] = $region->name;
					}
					break;
				case 'postalCode':
					$data[$field] = $record->postal->code;
					break;
				case 'city':
					$data[$field] = $record->city->name;
					break;
				case 'latitude':
					$data[$field] = $record->location->latitude;
					break;
				case 'longitude':
					$data[$field] = $record->location->longitude;
					break;
				case 'ip':
					$data[$field] = $record->traits->ipAddress;
					break;
				default:
					break;
			}
		}

		$this->data = array_merge($this->data, $data);

		return (object) $this->data;
	}

	private function initFields($fields = null)
	{
		if (is_null($fields))
		{
			return [
				'continentCode', 'continent',
				'countryCode', 'country',
				'regionCodes', 'regions',
				'postalCode',
			];
		}

		if (is_string($fields))
		{
			return [$fields];
		}

		if ( ! is_array($fields))
		{
			return false;
		}

		return $fields;
	}

	public function getRecordData()
	{
		if (empty($this->ip))
		{
			return false;
		}

		if (isset($this->records[$this->ip]))
		{
			return $this->records[$this->ip];
		}

		if ( ! $reader = $this->getReader())
		{
			return false;
		}

		try
		{
			$this->records[$this->ip] = $reader->city($this->ip);
		}
		catch (Exception $e)
		{
			$this->records[$this->ip] = false;
		}

		return $this->records[$this->ip];
	}

	private function getCountryName(City $record)
	{
		$country = $record->country->name;

		switch ($country)
		{
			case 'Russia':
				return 'Russian Federation';

			default:
				return $country;
		}
	}

	public function getReader()
	{
		if ( ! is_null($this->reader))
		{
			return $this->reader;
		}

		if ( ! self::hasDatabase())
		{
			return false;
		}

		$this->reader = new Reader(self::getDatabasePath());

		return $this->reader;
	}

	public static function hasDatabase()
	{
		$file = self::getDatabasePath();

		return file_exists($file)
			&& filesize($file) > 10;
	}
}
