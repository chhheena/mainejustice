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

use Joomla\CMS\Table\Table as JTable;

class AdvancedModulesTable extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__advancedmodules', 'moduleid', $db);
    }

    protected function _getAssetTitle()
    {
        if (isset($this->_title))
        {
            return $this->_title;
        }

        return $this->_getAssetName();
    }

    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_modules.module.' . (int) $this->{$k};
    }

    protected function getAssetParentId(JTable $table = null, $id = null)
    {
        // Initialise variables.
        $assetId = null;
        $db      = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__assets')
            ->where('name = ' . $db->quote('com_modules'));

        // Get the asset id from the database.
        $db->setQuery($query);
        $result = $db->loadResult();

        if ($result)
        {
            $assetId = (int) $result;
        }

        // Return the asset id.
        if ($assetId)
        {
            return $assetId;
        }
        else
        {
            return parent::_getAssetParentId($table, $id);
        }
    }
}

class AdvancedModulesTableAdvancedModules extends AdvancedModulesTable
{
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        return parent::getAssetParentId($table, $id);
    }
}
