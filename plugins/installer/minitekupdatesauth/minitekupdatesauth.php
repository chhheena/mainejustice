<?php
/**
* @title			Minitek Updates Authentication
* @copyright   		Copyright (C) 2011-2015 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   	https://www.akeebabackup.com/ - http://www.minitek.gr/
* @developers   	Nicholas K. Dionysopoulos - Edited by Minitek.gr
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Handle commercial extension update authorization
 *
 * @package     Joomla.Plugin
 * @subpackage  Installer.Example
 * @since       2.5
 */
class plgInstallerMinitekUpdatesAuth extends JPlugin
{
	/**
	 * Handle adding credentials to package download request
	 *
	 * @param   string $url     url from which package is going to be downloaded
	 * @param   array  $headers headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true if credentials have been added to request or not our business, false otherwise (credentials not set by user)
	 *
	 * @since   2.5
	*/
	 
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri = JUri::getInstance($url);
		// I don't care about download URLs not coming from our site
		// Note: as the Download ID is common for all extensions, this plugin will be triggered for all
		// extensions with a download URL on our site
		$host = $uri->getHost();
		
		if (!in_array($host, array('www.minitek.gr', 'update.minitek.gr')))
		{
			return true;
		}
		
		// Get the download ID from the plugin params
		$dlid = $this->params->get('minitek_download_id');
		
		// If the download ID is invalid, return without any further action
		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			return true;
		}
		
		// Append the Download ID to the download URL
		if (!empty($dlid))
		{
			$uri->setVar('dlid', $dlid);
			$url = $uri->toString();
		}
		
		return true;
	}
}