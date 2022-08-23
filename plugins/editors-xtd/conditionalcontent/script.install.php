<?php
/**
 * @package         Conditional Content
 * @version         4.0.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgEditorsXtdConditionalContentInstallerScript extends PlgEditorsXtdConditionalContentInstallerScriptHelper
{
    public $alias          = 'conditionalcontent';
    public $extension_type = 'plugin';
    public $name           = 'CONDITIONALCONTENT';
    public $plugin_folder  = 'editors-xtd';

    public function uninstall($adapter)
    {
        $this->uninstallPlugin($this->extname, 'system');
    }
}
