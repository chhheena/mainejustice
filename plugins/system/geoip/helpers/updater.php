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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\GeoIp\GeoIp as RL_GeoIp;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use splitbrain\PHPArchive\Tar;

require_once JPATH_LIBRARIES . '/geoip/autoload.php';

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class GeoIPUpdater
{
	private $database_file;
	private $database_name;
	private $date_file;
	private $force;
	private $license_key;
	private $package_file;
	private $temp_folder;

	public function update($name = 'City', $force = false)
	{
		jimport('joomla.filesystem.file');

		$params = RL_Parameters::getPlugin('geoip');

		$this->database_name = 'GeoLite2-' . $name;
		$this->database_file = JPATH_LIBRARIES . '/geoip/' . $this->database_name . '.mmdb';
		$this->temp_folder   = JFactory::getConfig()->get('tmp_path');
		$this->package_file  = $this->temp_folder . '/' . $this->database_name . '.tar.gz';
		$this->date_file     = JPATH_LIBRARIES . '/geoip/' . $this->database_name . '.date.txt';
		$this->license_key   = trim(JFactory::getApplication()->input->get('license', $params->maxmind_licence_key));
		$this->force         = $force;

		if ( ! $this->license_key)
		{
			return $this->error(JText::_('GEO_MESSAGE_ERROR_NO_LICENSE_KEY'));
		}

		$result = $this->download();

		if ($result->state == 'error')
		{
			return $result;
		}

		$this->deleteTempFiles();

		return $result;
	}

	private function error($message = '')
	{
		return (object) [
			'state'   => 'error',
			'message' => $message,
		];
	}

	private function download()
	{
		$last_date = $this->getVersion();

		if ( ! $this->force
			&& $last_date && date('Y-m') == date('Y-m', $last_date)
			&& RL_GeoIp::hasDatabase()
		)
		{
			return $this->success(JText::_('GEO_MESSAGE_UPTODATE'));
		}

		$url = 'https://download.maxmind.com/app/geoip_download'
			. '?edition_id=' . $this->database_name
			. '&suffix=tar.gz'
			. '&license_key=' . $this->license_key;

		$package = JHttpFactory::getHttp()->get($url, null, 30);

		if ( ! $package || $package->code != 200 || empty($package->body))
		{
			return $this->error($package->body ?: JText::_('GEO_MESSAGE_ERROR_UPDATE'));
		}

		$last_modified = isset($package->headers['last-modified'])
			? $package->headers['last-modified']
			: (
			isset($package->headers['Last-Modified'])
				? $package->headers['Last-Modified']
				: date('Y-m-d')
			);

		if ( ! $this->force
			&& $last_date
			&& strtotime($last_modified) <= $last_date
			&& RL_GeoIp::hasDatabase()
		)
		{
			return $this->success(JText::_('GEO_MESSAGE_UPTODATE'));
		}

		JFile::write($this->package_file, $package->body);

		$result = $this->unpack();

		if ($result->state == 'error')
		{
			return $result;
		}

		JFile::write($this->date_file, strtotime($last_modified));

		return $this->success();
	}

	public function deleteTempFiles()
	{
		JFile::delete($this->package_file);
	}

	public function getVersion()
	{
		if ( ! is_file($this->date_file))
		{
			return 0;
		}

		return file_get_contents($this->date_file);
	}

	private function success($message = '')
	{
		return (object) [
			'state'   => 'success',
			'message' => $message,
		];
	}

	private function unpack()
	{
		try
		{
			$tar = new Tar;
			$tar->open($this->package_file);
			$files = $tar->contents();

			$database_file = '';
			foreach ($files as $file)
			{
				if (strpos($file->getPath(), '.mmdb') === false)
				{
					continue;
				}

				$database_file = $file->getPath();
				break;
			}

			$tar = new Tar;
			$tar->open($this->package_file);
			$tar->extract($this->temp_folder);

			rename($this->temp_folder . '/' . $database_file, $this->database_file);

			JFolder::delete(dirname($this->temp_folder . '/' . $database_file));
		}
		catch (Exception $e)
		{
			return $this->error(JText::_('GEO_MESSAGE_ERROR_UNPACK'));
		}

		return $this->success();
	}
}
