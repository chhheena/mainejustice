<?php
/**
 * @package         Components Anywhere
 * @version         4.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemComponentsAnywhereInstallerScript extends PlgSystemComponentsAnywhereInstallerScriptHelper
{
	public $alias          = 'componentsanywhere';
	public $extension_type = 'plugin';
	public $name           = 'COMPONENTSANYWHERE';
}
