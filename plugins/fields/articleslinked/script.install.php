<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgFieldsArticlesLinkedInstallerScript extends PlgFieldsArticlesLinkedInstallerScriptHelper
{
	public $alias          = 'articleslinked';
	public $extension_type = 'plugin';
	public $name           = 'ARTICLESFIELD';
	public $plugin_folder  = 'fields';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin('articles', 'fields');
	}
}
