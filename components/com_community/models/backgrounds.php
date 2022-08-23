<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityModelBackgrounds extends JCCModel {

    private $backgrounds = array();
    public $enabled = true;

    /**
     * Load up all published moods on startup
     */
    public function __construct() {
        $this->enabled = CFactory::getConfig()->get("enablebackground");

        $db = JFactory::getDBO();
        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_backgrounds') . ' ORDER BY ' . $db->quoteName('ordering') . ' ASC';
        $db->setQuery($sql);

        $backgrounds = $db->loadObjectList();

        // build and pre-parse assoc result array
        foreach($backgrounds as $background)
        {   
            $imagefilename = "/background_".$background->id.".".$background->image;
            $thumbfilename = "/background_".$background->id."_thumb.".$background->image;

            // apply description translations for frontend
            $background->title = JText::_($background->title);
            $background->description = JText::_($background->description);

            if ($background->custom) {

                if( file_exists(COMMUNITY_STATUS_BACKGROUND_PATH.$imagefilename)) {
                    $background->image = rtrim(JURI::root(), '/').str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH).$imagefilename;
                } else {
                    $background->image ='';
                }

                if( file_exists(COMMUNITY_STATUS_BACKGROUND_PATH.$thumbfilename)) {
                    $background->thumb = rtrim(JURI::root(), '/').str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH).$thumbfilename;
                } else {
                    $background->thumb ='';
                }
            } else {
                if( file_exists(COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS.$imagefilename)) {
                    $background->image = rtrim(JURI::root(), '/').str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS).$imagefilename;
                } else {
                    $background->image ='';
                }

                if( file_exists(COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS.$thumbfilename)) {
                    $background->thumb = rtrim(JURI::root(), '/').str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS).$thumbfilename;
                } else {
                    $background->thumb ='';
                }
            }

            $this->backgrounds[$background->id] = $background;
        }

        unset($result);
    }

    /**
     * Return all backgrounds
     *
     * @return Array
     */
    public function getBackgrounds() {
        return $this->backgrounds;
    }

    /**
     * Load a single background
     *
     * @param string|int $moodId
     *
     * @return Object
     */
    public function getBackground($backgroundId) {

        $backgroundId = strtolower($backgroundId);

        if(array_key_exists($backgroundId, $this->backgrounds)) return $this->backgrounds[$backgroundId];

        return false;
    }
}
