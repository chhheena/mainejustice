<?php
/**
 * @package         ReReplacer
 * @version         12.4.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgActionlogReReplacerInstallerScript extends PlgActionlogReReplacerInstallerScriptHelper
{
    public $alias          = 'rereplacer';
    public $extension_type = 'plugin';
    public $name           = 'REREPLACER';
    public $plugin_folder  = 'actionlog';

    public function uninstall($adapter)
    {
        $this->uninstallComponent($this->extname);
    }
}
