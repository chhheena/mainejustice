<?php
/**
 * @package      JoomGrabber
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2020 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
use \Joomla\CMS\Router\Router;

// Initialize Joomla framework
const _JEXEC = 1;
if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';


/**
 * Cron job to process JoomGrabber Grabbed Items
 * @since      1.0
 */
class JoomGrabberCron extends JApplicationCli
{
    /**
     * Entry point for the script
     *
     */
    public function doExecute()
    {

        // Remove the script time limit.
        @set_time_limit(0);

        // Fool the system into thinking we are running as JSite with Smart Search as the active component.
        $_SERVER['HTTP_HOST'] = 'domain.com';
        JFactory::getApplication('site');

        require_once JPATH_SITE . DS . 'components' . DS . 'com_joomgrabber' . DS . 'cronjob.php';
        require_once JPATH_SITE . DS . 'components' . DS . 'com_joomgrabber' . DS . 'plugin.php';


        ogbCronCallAIO::run(true);
    }

}


JApplicationCli::getInstance('JoomGrabberCron')->execute();

