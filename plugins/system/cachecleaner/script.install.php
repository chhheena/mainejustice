<?php
/**
 * @package         Cache Cleaner
 * @version         8.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemCacheCleanerInstallerScript extends PlgSystemCacheCleanerInstallerScriptHelper
{
	public $alias          = 'cachecleaner';
	public $extension_type = 'plugin';
	public $name           = 'CACHECLEANER';

	public function onAfterInstall($route)
	{
		$this->deleteOldFiles();
		$this->setCorrectCloudFlareMethod();

		return parent::onAfterInstall($route);
	}

	public function uninstall($adapter)
	{
		$this->uninstallModule($this->extname);
	}

	private function deleteOldFiles()
	{
		parent::delete([
			JPATH_SITE . '/plugins/system/cachecleaner/Cache/MaxCDN.php',
			JPATH_SITE . '/plugins/system/cachecleaner/Api/NetDNA.php',
			JPATH_SITE . '/plugins/system/cachecleaner/Api/OAuth',
		]);
	}

	private function setCorrectCloudFlareMethod()
	{
		$query = $this->db->getQuery(true)
			->select('params')
			->from('#__extensions')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('cachecleaner'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'));
		$this->db->setQuery($query);

		$params = $this->db->loadResult();

		$params = json_decode($params);

		// return if the new param key is found
		if (isset($params->clean_cloudflare_authorization_method))
		{
			return;
		}

		// return if the cloudflare username is not in use
		if (empty($params->cloudflare_username))
		{
			return;
		}

		$params->clean_cloudflare_authorization_method = 'username';

		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('params') . ' = ' . $this->db->quote(json_encode($params)))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('cachecleaner'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'));
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
