<?php
/**
 * @package         Email Protector
 * @version         4.7.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemEmailProtectorInstallerScript extends PlgSystemEmailProtectorInstallerScriptHelper
{
	public $alias          = 'emailprotector';
	public $extension_type = 'plugin';
	public $name           = 'EMAILPROTECTOR';

	public function onAfterInstall($route)
	{
		$this->disableCoreEmailCloaker();

		return parent::onAfterInstall($route);
	}

	private function disableCoreEmailCloaker()
	{
		// Disable the core Email Cloaking plugin
		$query = $this->db->getQuery(true)
			->update('#__extensions as e')
			->set('e.enabled = 0')
			->where('e.name = ' . $this->db->quote('plg_content_emailcloak'));
		$this->db->setQuery($query);
		$this->db->execute();

		JFactory::getCache()->clean('_system');
	}
}
