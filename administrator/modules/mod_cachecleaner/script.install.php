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

class Mod_CacheCleanerInstallerScript extends Mod_CacheCleanerInstallerScriptHelper
{
	public $alias           = 'cachecleaner';
	public $client_id       = 1;
	public $extension_type  = 'module';
	public $module_position = 'status';
	public $name            = 'CACHECLEANER';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
	}
}
