<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemAdvancedTemplatesInstallerScript extends PlgSystemAdvancedTemplatesInstallerScriptHelper
{
	public $alias          = 'advancedtemplatemanager';
	public $extension_type = 'plugin';
	public $extname        = 'advancedtemplates';
	public $name           = 'ADVANCEDTEMPLATEMANAGER';

	public function onAfterInstall($route)
	{
		$this->setPluginOrdering();

		return parent::onAfterInstall($route);
	}

	public function uninstall($adapter)
	{
		$this->uninstallComponent($this->extname);
		$this->uninstallPlugin($this->extname, 'actionlog');
	}

	private function setPluginOrdering()
	{
		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('ordering') . ' = 1')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('advancedtemplates'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'));
		$this->db->setQuery($query);
		$this->db->execute();

		JFactory::getCache()->clean('_system');
	}
}
