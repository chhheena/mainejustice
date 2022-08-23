<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.1.3
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\ActionLogPlugin as RL_ActionLogPlugin;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Log as RL_Log;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ActionLogPlugin.php')
)
{
    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
    RL_Extension::disable('regularlabsmanager', 'plugin', 'actionlog');

    return;
}

if (true)
{
    class PlgActionlogRegularLabsManager extends RL_ActionLogPlugin
    {
        public $name  = 'REGULARLABSEXTENSIONMANAGER';
        public $alias = 'regularlabsmanager';

        public function onExtensionAfterInstall($installer, $eid)
        {
            // Prevent duplicate logs
            if (in_array('install_' . $eid, self::$ids))
            {
                return;
            }

            $context = JFactory::getApplication()->input->get('option');

            if (strpos($context, $this->option) === false)
            {
                return;
            }

            if ( ! RL_Array::find(['*', 'install'], $this->events))
            {
                return;
            }

            $extension = RL_Extension::getById($eid);

            if (empty($extension->manifest_cache))
            {
                return;
            }

            $manifest = json_decode($extension->manifest_cache);

            if (empty($manifest->name))
            {
                return;
            }

            self::$ids[] = 'install_' . $eid;

            $message = [
                'id'             => $eid,
                'extension_name' => JText::_($manifest->name),
            ];

            RL_Log::install($message, 'com_regularlabsmanager', $manifest->type);
        }
    }
}
