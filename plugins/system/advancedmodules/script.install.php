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

use Joomla\CMS\Factory as JFactory;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemAdvancedModulesInstallerScript extends PlgSystemAdvancedModulesInstallerScriptHelper
{
    public $alias          = 'advancedmodulemanager';
    public $extension_type = 'plugin';
    public $extname        = 'advancedmodules';
    public $name           = 'ADVANCEDMODULEMANAGER';

    public function onAfterInstall($route)
    {
        $this->setPluginOrdering();

        return parent::onAfterInstall($route);
    }

    private function setPluginOrdering()
    {
        $query = $this->db->getQuery(true)
            ->update('#__extensions')
            ->set($this->db->quoteName('ordering') . ' = -1')
            ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
            ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('advancedmodules'))
            ->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'));
        $this->db->setQuery($query);
        $this->db->execute();

        JFactory::getCache()->clean('_system');
    }

    public function uninstall($adapter)
    {
        $this->uninstallComponent($this->extname);
        $this->uninstallPlugin($this->extname, 'actionlog');
    }
}
