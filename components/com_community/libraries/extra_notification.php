<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

class CExtraNotification{

    static $instance = null;
    private $notificationTypes = array();
    private $notificationSettings = array();

    public function __construct($populatedTypes = array())
    {
        if($this::$instance != null){
            return $this;
        }
        $this::$instance = $this;

        //when first loaded, lets check if there is any value from
        $this->loadExtraNotifications($populatedTypes);

    }


    private function loadExtraNotifications($populatedTypes = array()){
        $appsLib	= CAppPlugins::getInstance();
		$appsLib->loadApplications();
        $notifications = array($populatedTypes);
        $notifications = $appsLib->triggerEvent('onLoadingExtraNotifications', $notifications);
        if(isset($notifications[0]) && count($notifications[0]) > 0){
            $this->notificationTypes = $notifications[0];
        }else{
            $this->notificationTypes = $populatedTypes;
        }
    }

    public function getNotificationTypes(){
        return $this->notificationTypes;
    }

    public function getNotificationSettings(){
        return $this->notificationSettings;
    }

}