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

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use PlgSystemAdvancedModuleHelper;

class ModuleHelper
{
    public static function registerEvents()
    {
        require_once __DIR__ . '/Helpers/advancedmodulehelper.php';
        $class = new PlgSystemAdvancedModuleHelper;

        JFactory::getApplication()->registerEvent('onRenderModule', [$class, 'onRenderModule']);
        JFactory::getApplication()->registerEvent('onPrepareModuleList', [$class, 'onPrepareModuleList']);
    }
}
