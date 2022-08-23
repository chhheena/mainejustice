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

use Joomla\CMS\Form\FormField as JFormField;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\GeoIp\GeoIp as RL_GeoIp;

require_once JPATH_LIBRARIES . '/geoip/autoload.php';

class JFormFieldGeoIP_Version extends JFormField
{
	protected $type = 'Version';

	protected function getInput()
	{
		$file = JPATH_LIBRARIES . '/geoip/GeoLite2-City.date.txt';

		if ( ! RL_GeoIp::hasDatabase())
		{
			return '-';
		}

		if ( ! file_exists($file)
			|| ! $last_date = file_get_contents($file)
		)
		{
			return '-';
		}

		return '<span class="label">' . JHtml::_('date', $last_date, JText::_('DATE_FORMAT_LC3')) . '</span>';
	}
}
