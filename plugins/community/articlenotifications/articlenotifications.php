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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

if(!class_exists('plgCommunityArticleNotifications'))
{
	class plgCommunityArticleNotifications extends CApplications
	{
		var $name		= 'ArticleNotifications';
		var $_name		= 'articlenotifications';
		
	    function __construct(& $subject, $config)
	    {
			parent::__construct($subject, $config);
	    }

        public function onLoadingExtraNotifications($data){

            //first parameter : OTHERS, PROFILE, GROUPS, EVENTS, VIDEOS, PHOTOS
            //second parameter : option value(make sure its unique), format : [module]_[action] example : profile_activity_add_comment, For likes, make sure it's [module]_[action]_like
            //third parameter : Language string for the label
            //fourth paramter : Language string for the label tips
            $data = array_merge(array(
                array('OTHERS', 'article_comment', JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATION_ARTICLE_COMMENT'), JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATION_ARTICLE_COMMENT_TIPS')),
                array('OTHERS', 'article_comment_like', JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATION_ARTICLE_COMMENT_LIKE'), JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATION_ARTICLE_COMMENT_LIKE_TIPS'))
            ), $data);

            return $data;
        }

	}	
}


