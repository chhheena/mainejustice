<?php
/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die;

class mxYouTuber_Model_Base{
    
    private $_cofing = null;
    
    public function __construct(){
        
    }

    public function getConfig(){
        if(is_null($this->_cofing)){
            $plugin = JPluginHelper::getPlugin('system', 'youtuber');
            $this->_cofing = new JRegistry($plugin->params);
        }
        return $this->_cofing;
    }

}
