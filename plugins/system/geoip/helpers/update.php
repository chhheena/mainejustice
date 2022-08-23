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
use RegularLabs\Library\Language as RL_Language;

RL_Language::load('plg_system_geoip');

require_once __DIR__ . '/updater.php';
$updater = new GeoIPUpdater;

$force = JFactory::getApplication()->input->getInt('force');

$result = $updater->update('City', $force);

if ($result->state == 'error')
{
	die('-' . $result->message);
}

if ($result->message)
{
	die('+' . $result->message);
}

if ( ! $last_date = $updater->getVersion())
{
	die();
}

$message = '+' . JText::sprintf('GEO_MESSAGE_UPDATED_TO', JHtml::_('date', $last_date, JText::_('DATE_FORMAT_LC3')));

die($message);
