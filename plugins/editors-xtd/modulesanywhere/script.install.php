<?php
/**
 * @package         Modules Anywhere
 * @version         7.15.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgEditorsXtdModulesAnywhereInstallerScript extends PlgEditorsXtdModulesAnywhereInstallerScriptHelper
{
	public $alias          = 'modulesanywhere';
	public $extension_type = 'plugin';
	public $name           = 'MODULESANYWHERE';
	public $plugin_folder  = 'editors-xtd';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
	}
}
