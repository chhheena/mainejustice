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

class PlgFieldsArticlesInstallerScript extends PlgFieldsArticlesInstallerScriptHelper
{
	public $alias          = 'articles';
	public $extension_type = 'plugin';
	public $name           = 'ARTICLESFIELD';
	public $plugin_folder  = 'fields';

	public function onAfterInstall($route)
	{
		$this->moveOldLinkedTypesToNew();

		return parent::onAfterInstall($route);
	}

	public function uninstall($adapter)
	{
		$this->uninstallPlugin('articleslinked', 'fields');
	}

	private function moveOldLinkedTypesToNew()
	{
		$query = $this->db->getQuery(true)
			->update('#__fields')
			->set($this->db->quoteName('type') . ' = ' . $this->db->quote('articleslinked'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('articles'))
			->where($this->db->quoteName('fieldparams') . ' LIKE ' . $this->db->quote('%"field_type":"linked_articles"%'));
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
