<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallHelperUtilities
{
	/**
	 * Configure the Sidebar menu.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_MINITEKWALL_DASHBOARD'),
			'index.php?option=com_minitekwall',
			$vName == 'dashboard'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_MINITEKWALL_WIDGETS'),
			'index.php?option=com_minitekwall&view=widgets',
			$vName == 'widgets'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_MINITEKWALL_CUSTOM_ITEMS'),
			'index.php?option=com_minitekwall&view=items',
			$vName == 'items'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_MINITEKWALL_GROUPS'),
			'index.php?option=com_minitekwall&view=groups',
			$vName == 'groups'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_MINITEKWALL_CUSTOM_GRIDS'),
			'index.php?option=com_minitekwall&view=grids',
			$vName == 'grids'
		);
	}

	public static function getActions($categoryId = 0, $articleId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($articleId) && empty($categoryId))
		{
			$assetName = 'com_minitekwall';
		}
		else
		{
			$assetName = 'com_minitekwall.widget.'.(int) $articleId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function checkModuleIsInstalled()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('*')
			->from('#__extensions AS e');
		$query->where('e.element = ' . $db->quote('mod_minitekwall'));

		// Setup the query
		$db->setQuery($query);

		$moduleExists = $db->loadObject();

		if ($moduleExists)
			return true;

		return false;
	}

	public function latestVersion()
	{
		$params = JComponentHelper::getParams('com_minitekwall');
		$version = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_wall_pro.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '3.*')
				{
					$version = (string)$update->version;
					break;
				}
			}
		}

		return $version;
	}

	public function localVersion()
	{
		$xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_minitekwall/minitekwall.xml');
		$version = (string)$xml->version;

		return $version;
	}

	public static function getMinitekAuthPlugin()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('installer'))
			->where($db->quoteName('element') . ' = ' . $db->quote('minitekupdatesauth'));
		$db->setQuery($query);

		$result = $db->loadObject();

		if (!$result)
			return false;

		$registry = new JRegistry;
		$registry->loadString($result->params);
		$params = $registry->toArray();
		$download_id = $params['minitek_download_id'];

		return $download_id ? 'active' : $result->extension_id;
	}

	/**
	 * Method to save the Download ID.
	 * 
	 * @return	bool
	 * 
	 * @since   3.9.4
	 */
	public static function saveDownloadId($id, $download_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('params') . ' = \'{"minitek_download_id":"'.$download_id.'"}\'');
		$query->where($db->quoteName('extension_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);

		if ($result = $db->execute())
		{
			return true;
		}

		return false;
	}
}
