<?php
/**
 * @package         Add to Menu
 * @version         6.7.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class Mod_AddtoMenuInstallerScript extends Mod_AddtoMenuInstallerScriptHelper
{
	public $alias           = 'addtomenu';
	public $client_id       = 1;
	public $extension_type  = 'module';
	public $module_position = 'status';
	public $name            = 'ADDTOMENU';
}
