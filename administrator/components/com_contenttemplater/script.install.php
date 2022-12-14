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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\AbstractRegistryFormat as JRegistryFormat;

require_once __DIR__ . '/script.install.helper.php';

class Com_ContentTemplaterInstallerScript extends Com_ContentTemplaterInstallerScriptHelper
{
	public $alias          = 'contenttemplater';
	public $extension_type = 'component';
	public $name           = 'CONTENTTEMPLATER';

	public function onAfterInstall($route)
	{
		$this->createTable();
		$this->fixOldFormatInDatabase();
		$this->deleteOldFiles();

		return parent::onAfterInstall($route);
	}

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
		$this->uninstallPlugin($this->extname, 'editors-xtd');
		$this->uninstallPlugin($this->extname, 'actionlog');
	}

	private function checkForGeoIP()
	{
	}

	private function createTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS `#__contenttemplater` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(100) NOT NULL,
			`description` TEXT NOT NULL,
			`category` VARCHAR(50) NOT NULL,
			`content` MEDIUMTEXT NOT NULL,
			`params` TEXT NOT NULL,
			`published` TINYINT(1) NOT NULL DEFAULT '0',
			`ordering` INT NOT NULL DEFAULT '0',
			`checked_out` INT UNSIGNED NOT NULL DEFAULT '0',
			`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (`id`),
			KEY `id` (`id`,`published`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	private function deleteOldFiles()
	{
		$this->delete(
			[
				JPATH_SITE . '/components/com_contenttemplater',
			]
		);
	}

	private function fixOldFormatInDatabase()
	{
		$query = 'SHOW FIELDS FROM ' . $this->db->quoteName('#__contenttemplater');
		$this->db->setQuery($query);
		$columns = $this->db->loadColumn();

		if ( ! in_array('category', $columns))
		{
			$query = 'ALTER TABLE ' . $this->db->quoteName('#__contenttemplater')
				. ' CHANGE COLUMN `name` `name` VARCHAR(100) NOT NULL AFTER `id`,'
				. ' ADD COLUMN `category` VARCHAR(50) NOT NULL AFTER `description`';
			$this->db->setQuery($query);
			$this->db->query();
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__contenttemplater` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` TEXT NOT NULL,
			`description` TEXT NOT NULL,
			`content` MEDIUMTEXT NOT NULL,
			`params` TEXT NOT NULL,
			`published` TINYINT(1) NOT NULL DEFAULT '0',
			`ordering` INT NOT NULL DEFAULT '0',
			`checked_out` INT UNSIGNED NOT NULL DEFAULT '0',
			`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (`id`),
			KEY `id` (`id`,`published`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->setQuery($query);
		$this->db->execute();

		// convert old J1.5 params syntax to new
		$query = $this->db->getQuery(true)
			->select('c.id, c.params')
			->from('#__contenttemplater as c')
			->where('c.params REGEXP ' . $this->db->quote('^[^\{]'));
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();

		foreach ($rows as $row)
		{
			if (empty($row->params))
			{
				continue;
			}

			$row->params = str_replace('assignto_secscats', 'assignto_cats', $row->params);
			$row->params = str_replace('flexicontent', 'fc', $row->params);

			$params = JRegistryFormat::getInstance('INI')->stringToObject($row->params);
			foreach ($params as $key => $val)
			{
				if (is_string($val) && ! (strpos($val, '|') === false))
				{
					$params->{$key} = explode('|', $val);
				}
			}

			if ( ! empty($params->assignto_cats_selection))
			{
				foreach ($params->assignto_cats_selection as $key => $val)
				{
					if ( ! (strpos($val, ':') === false))
					{
						$params->assignto_cats_selection[$key] = substr($val, strpos($val, ':') + 1);
					}
				}
			}

			$query = $this->db->getQuery(true)
				->update('#__contenttemplater as c')
				->set('c.params = ' . $this->db->quote(json_encode($params)))
				->where('c.id = ' . (int) $row->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}

		// concatenates the sef and non-sef url fields
		$query = $this->db->getQuery(true);
		$query->update('#__contenttemplater as c')
			->set(
				'c.params = replace( replace( replace( replace( `params`,'
				. $this->db->quote('"assignto_urls_selection_sef"') . ',' . $this->db->quote('"assignto_urls_selection"') . '),'
				. $this->db->quote('"assignto_urls_selection":"","assignto_browsers"') . ',' . $this->db->quote('"assignto_browsers"') . '),'
				. $this->db->quote('","show_url_field":"0","assignto_urls_selection":"') . ',' . $this->db->quote('\n') . '),'
				. $this->db->quote('","show_url_field":"1","assignto_urls_selection":"') . ',' . $this->db->quote('\n') . ')'
			)
			->where('c.params LIKE ' . $this->db->quote('%"assignto_urls_selection_sef"%'));
		$this->db->setQuery($query);
		$this->db->execute();

		// add url_regex value to filled in url fields
		$query = $this->db->getQuery(true)
			->update('#__contenttemplater as c')
			->set(
				'c.params = replace( replace( replace( replace( `params`,'
				. $this->db->quote('"assignto_os"') . ',' . $this->db->quote('"assignto_urls_regex":"1","assignto_os"') . '),'
				. $this->db->quote('"","assignto_urls_regex":"1"') . ',' . $this->db->quote('""') . '),'
				. $this->db->quote('"assignto_urls_regex":"0","assignto_urls_regex":"1"') . ',' . $this->db->quote('"assignto_urls_regex":"0"') . '),'
				. $this->db->quote('"assignto_urls_regex":"1","assignto_urls_regex":"1"') . ',' . $this->db->quote('"assignto_urls_regex":"1"') . ')'
			)
			->where('c.params LIKE ' . $this->db->quote('%"assignto_urls":"1"%'))
			->where('c.params NOT LIKE ' . $this->db->quote('%"assignto_urls_regex"%'));
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
