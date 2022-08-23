<?php
/**
 * @package         Advanced Module Manager
 * @version         9.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgActionlogAdvancedModulesInstallerScript extends PlgActionlogAdvancedModulesInstallerScriptHelper
{
    public $alias          = 'advancedmodules';
    public $extension_type = 'plugin';
    public $name           = 'ADVANCEDMODULEMANAGER';
    public $plugin_folder  = 'actionlog';

    public function uninstall($adapter)
    {
        $this->uninstallComponent($this->extname);
        $this->uninstallPlugin($this->extname, 'system');
    }
}
