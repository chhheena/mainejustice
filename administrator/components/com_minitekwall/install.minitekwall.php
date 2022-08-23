<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class com_minitekwallInstallerScript
{
	function preflight($type, $parent)
	{
		if (is_object($this->getOldVersion()))
		{
			// Get old version
			$this->old_version = $this->getOldVersion()->version;

			// Update script < 3.8.3
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.8.3', '<'))
			{
				self::update383($parent);
			}

			// Update script < 3.9.1 (custom items and groups)
			self::update391($parent);

			// Update script < 3.9.2 (custom grids)
			self::update392($parent);
		}
	}

	function update392($parent)
	{
		$db = JFactory::getDbo();

		// Create #__minitek_wall_grids table
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_wall_grids` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`asset_id` int(10) unsigned NOT NULL DEFAULT '0',
			`name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
			`columns` tinyint(3) unsigned NOT NULL,
			`state` tinyint(3) NOT NULL DEFAULT '0',
			`checked_out` int(10) unsigned NOT NULL DEFAULT '0',
			`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`elements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; ";

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			die('Error creating table #__minitek_wall_grids.');
		}
	}

	function update391($parent)
	{
		$db = JFactory::getDbo();

		// Add custom_source column in __minitek_wall_widgets_source
		$columns = $db->getTableColumns('#__minitek_wall_widgets_source');
		if (!isset($columns['custom_source']))
		{
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_wall_widgets_source` ADD `custom_source` text NOT NULL ";

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				die('Error inserting custom_source column.');
			}
		}

		// Create #_minitek_source_groups table
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_source_groups` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`asset_id` int(10) unsigned NOT NULL DEFAULT '0',
			`name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
			`description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
			`state` tinyint(3) NOT NULL DEFAULT '0',
			`checked_out` int(10) unsigned NOT NULL DEFAULT '0',
			`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8; ";

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			die('Error creating table #__minitek_source_groups.');
		}

		// Create #_minitek_source_items table
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_source_items` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`asset_id` int(10) unsigned NOT NULL DEFAULT '0',
			`title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
			`description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
			`category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
			`author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
			`state` tinyint(3) NOT NULL DEFAULT '0',
			`groupid` int(10) unsigned NOT NULL DEFAULT '0',
			`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`created_by` int(10) unsigned NOT NULL DEFAULT '0',
			`created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
			`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`modified_by` int(10) unsigned NOT NULL DEFAULT '0',
			`checked_out` int(10) unsigned NOT NULL DEFAULT '0',
			`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`images` text COLLATE utf8mb4_unicode_ci NOT NULL,
			`urls` text COLLATE utf8mb4_unicode_ci NOT NULL,
			`tags` text COLLATE utf8mb4_unicode_ci NOT NULL,
			`ordering` int(11) NOT NULL DEFAULT '0',
			`access` int(10) unsigned NOT NULL DEFAULT '0',
			`featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; ";

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			die('Error creating table #__minitek_source_items.');
		}
	}

	function update383($parent)
	{
		$db = JFactory::getDbo();
		$columns = $db->getTableColumns('#__minitek_wall_widgets_source');

		// Add rss_source column in __minitek_wall_widgets_source
		if (!isset($columns['rss_source']))
		{
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_wall_widgets_source` ADD `rss_source` text NOT NULL ";

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				die('Error inserting rss_source column.');
			}
		}

		// Add easysocial_source column in __minitek_wall_widgets_source
		if (!isset($columns['easysocial_source']))
		{
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_wall_widgets_source` ADD `easysocial_source` text NOT NULL ";

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				die('Error inserting easysocial_source column.');
			}
		}
	}

	function install($parent)
	{}

	function update($parent)
	{}

	function uninstall($parent)
	{}

	function postflight($type, $parent)
	{}

	private static function getOldVersion()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT manifest_cache FROM '.$db->quoteName('#__extensions');
		$query .= ' WHERE '.$db->quoteName('element').' = '.$db->quote('com_minitekwall').' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			$manifest_cache = json_decode($row->manifest_cache, false);
			return $manifest_cache;
		}
		else
		{
			return false;
		}
	}
}
