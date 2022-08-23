<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.1.3
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgActionlogRegularLabsManagerInstallerScript extends PlgActionlogRegularLabsManagerInstallerScriptHelper
{
    public $alias          = 'regularlabsmanager';
    public $extension_type = 'plugin';
    public $name           = 'REGULARLABSEXTENSIONMANAGER';
    public $plugin_folder  = 'actionlog';

    public function uninstall($adapter)
    {
        $this->uninstallComponent($this->extname);
    }
}
