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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\CMSPlugin as JPlugin;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\ParametersNew as RL_Parameters;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ParametersNew.php')
)
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'GEOIP'))
{
	RL_Extension::disable('geoip', 'plugin');

	RL_Document::adminError(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('GEOIP'))
	);

	return;
}

if (true)
{
	class PlgSystemGeoIP extends JPlugin
	{
		static $messages_sent = [];

		public function __construct(&$subject, $config)
		{
			// only in admin (and not on login page)
			if ( ! RL_Document::isAdmin(true))
			{
				return;
			}

			// if geoip_update=1 is found in the url
			if (JFactory::getApplication()->input->getInt('geoip_update'))
			{
				include __DIR__ . '/helpers/update.php';

				return;
			}

			parent::__construct($subject, $config);

			$params = RL_Parameters::getPlugin('geoip');

			if ( ! $params->show_notices)
			{
				return;
			}

			$this->loadLanguage();

			$database_file = JPATH_LIBRARIES . '/geoip/GeoLite2-City.mmdb';
			$url           = 'index.php?option=com_plugins&filter_folder=&filter_search=' . JText::_('PLG_SYSTEM_GEOIP');

			// Check if the database file exists and is not empty
			if ( ! $this->hasDatabase($database_file))
			{
				$message = 'GEO_MESSAGE_WARNING_NO_DATABASE_FILE';

				if (in_array($message, self::$messages_sent))
				{
					return;
				}

				JFactory::getApplication()->enqueueMessage(
					JText::sprintf($message, '<a href="' . $url . '">', '</a>'),
					'warning'
				);

				self::$messages_sent[] = $message;

				return;
			}

			// Show message if database is older than 6 months (so outdated)
			$six_months = (365 * 24 * 60 * 60) / 2;
			if (time() - filemtime($database_file) > $six_months)
			{
				$message = 'GEO_MESSAGE_WARNING_DATABASE_FILE_OUTDATED';

				if (in_array($message, self::$messages_sent))
				{
					return;
				}

				JFactory::getApplication()->enqueueMessage(
					JText::sprintf($message, '<a href="' . $url . '">', '</a>'),
					'warning'
				);

				self::$messages_sent[] = $message;

				return;
			}
		}

		private function hasDatabase($database_file)
		{
			return file_exists($database_file)
				&& filesize($database_file) > 10;
		}
	}
}
