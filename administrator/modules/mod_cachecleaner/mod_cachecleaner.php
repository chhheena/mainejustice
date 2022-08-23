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

use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;

/**
 * Module that cleans cache
 */

// return if Regular Labs Library plugin is not installed
jimport('joomla.filesystem.file');
if (
	! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
	return;
}

// return if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
	return;
}

// return if Cache Cleaner system plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'cachecleaner'))
{
	return;
}

if (true)
{
	// Include the syndicate functions only once
	require_once __DIR__ . '/helper.php';

	$helper = new ModCacheCleaner;
	$helper->render();
}
