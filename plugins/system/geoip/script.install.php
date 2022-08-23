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
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemGeoIPInstallerScript extends PlgSystemGeoIPInstallerScriptHelper
{
	public $alias          = 'geoip';
	public $extension_type = 'plugin';
	public $name           = 'GeoIP';

	public function onAfterInstall($route)
	{
		$this->delete(
			[
				JPATH_LIBRARIES . '/geoip/geoip2',
				JPATH_LIBRARIES . '/geoip/maxmind',
			]
		);

		require_once JPATH_PLUGINS . '/system/geoip/helpers/updater.php';
		$updater = new GeoIPUpdater;

		$result = $updater->update('City', true);

		if ($result->state == 'error')
		{
			JFactory::getApplication()->enqueueMessage($result->message, 'error');
		}

		if ( ! $result->message && $last_date = $updater->getVersion())
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('GEO_MESSAGE_UPDATED_TO', JHtml::_('date', $last_date, JText::_('DATE_FORMAT_LC3')))
			);
		}

		return parent::onAfterInstall($route);
	}

	public function uninstall($adapter)
	{
		$this->delete(
			[
				JPATH_LIBRARIES . '/geoip',
			]
		);
	}
}
