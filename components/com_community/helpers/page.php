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

class CPageHelper
{

	static public function getPostCount($pageid){
		$db = JFactory::getDbo();

		$query = "SELECT count(*) as count FROM ".$db->quoteName('#__community_activities')
				." WHERE ".$db->quoteName('app')."=".$db->quote('pages.wall')
				." AND ".$db->quoteName('pageid')."=".$pageid;

		$db->setQuery($query);

		return $db->loadResult();
	}

	static public function getMediaPermission( $pageId )
	{
		// load COwnerHelper::isCommunityAdmin()
		//CFactory::load( 'helpers' , 'owner' );
		$my	= CFactory::getUser();

		$isSuperAdmin		= COwnerHelper::isCommunityAdmin();
		$isAdmin			= false;
		$isMember			= false;
		$waitingApproval	= false;

		// Load the page table.
		$pageModel	= CFactory::getModel( 'pages' );
		$page		= JTable::getInstance( 'Page' , 'CTable' );
		$page->load( $pageId );
		$params		= $page->getParams();

		if(!$isSuperAdmin)
		{
			$isAdmin	= $pageModel->isAdmin( $my->id , $page->id );
			$isMember	= $page->isMember( $my->id );

			//check if awaiting page's approval
			if( $pageModel->isWaitingAuthorization( $my->id , $page->id ) )
			{
				$waitingApproval	= true;
			}
		}

		$permission = new stdClass();
		$permission->isMember 			= $isMember;
		$permission->waitingApproval 	= $waitingApproval;
		$permission->isAdmin 			= $isAdmin;
		$permission->isSuperAdmin 		= $isSuperAdmin;
		$permission->params 			= $params;
		$permission->privatePage		= $page->approvals;

		return $permission;
	}

	static public function allowViewMedia( $pageId )
	{
		if(empty($pageId))
		{
			return false;
		}

		//get permission
		$permission = CPageHelper::getMediaPermission($pageId);

		if($permission->privatePage)
		{
			if($permission->isSuperAdmin || ($permission->isMember && !$permission->waitingApproval) )
			{
				$allowViewVideos = true;
			}
			else
			{
				$allowViewVideos = false;
			}
		}
		else
		{
			$allowViewVideos = true;
		}

		return $allowViewVideos;
	}

	static public function allowManageVideo( $pageId )
	{
		$allowManageVideos = false;

		//get permission
		$permission = CPageHelper::getMediaPermission($pageId);

		$videopermission	= $permission->params->get('videopermission' , PAGE_VIDEO_PERMISSION_ADMINS );

		//checking for backward compatibility
                if($videopermission == PAGE_VIDEO_PERMISSION_ALL)
                {
                    $videopermission = PAGE_VIDEO_PERMISSION_MEMBERS;
                }

		if($videopermission == PAGE_VIDEO_PERMISSION_DISABLE)
		{
			$allowManageVideos = false;
		}
		else if( ($videopermission == PAGE_VIDEO_PERMISSION_MEMBERS && $permission->isMember && !$permission->waitingApproval) || $permission->isAdmin || $permission->isSuperAdmin )
		{
			$allowManageVideos = true;
		}

		return $allowManageVideos;
	}

	static public function allowManagePhoto($pageId)
	{
		$allowManagePhotos = false;

		//get permission
		$permission = CPageHelper::getMediaPermission($pageId);
		$photopermission = $permission->params->get('photopermission' , PAGE_PHOTO_PERMISSION_ADMINS );

        //checking for backward compatibility
        if($photopermission == PAGE_PHOTO_PERMISSION_ALL) {
            $photopermission = PAGE_PHOTO_PERMISSION_MEMBERS;
        }

		if($photopermission == PAGE_PHOTO_PERMISSION_DISABLE) {
			$allowManagePhotos = false;
		} else if( ($photopermission == PAGE_PHOTO_PERMISSION_MEMBERS && $permission->isMember && !$permission->waitingApproval) || $permission->isAdmin || $permission->isSuperAdmin) {
			$allowManagePhotos = true;
		}

		return $allowManagePhotos;
	}
	static public function allowManageEvent( $userId , $pageId , $eventId )
	{
		//CFactory::load( 'helpers' , 'owner' );
		$user		= CFactory::getUser( $userId );
		$page		= JTable::getInstance( 'Page' , 'CTable' );
		$event		= JTable::getInstance( 'Event' , 'CTable' );

		$event->load( $eventId );
		$page->load( $pageId );

		if( COwnerHelper::isCommunityAdmin() || $page->isAdmin( $user->id ) || $event->isCreator( $user->id ) )
		{
			return true;
		}
		return false;
	}

	static public function allowCreateEvent( $userId , $pageId )
	{
		//CFactory::load( 'helpers' , 'owner' );
		$user		= CFactory::getUser( $userId );
		$page		= JTable::getInstance( 'Page' , 'CTable' );
		$page->load( $pageId );

		$params		= $page->getParams();

        if (COwnerHelper::isCommunityAdmin()) {
            return true;
        }

		if( $page->isAdmin( $user->id ) && ( $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ALL || $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ADMINS ) )
		{
			return true;
		}

		if( $page->isMember( $user->id ) && $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ALL )
		{
			return true;
		}

		return false;
	}

	static public function allowPhotoWall($pageid)
	{
		$permission = CPageHelper::getMediaPermission($pageid);

		if( $permission->isMember || $permission->isAdmin || $permission->isSuperAdmin )
		{
			return true;
		}
		return false;
	}
}

/**
 * Deprecated since 1.8
 * Use CPageHelper::getMediaPermission instead.
 */
function _cGetPageMediaPermission($pageId)
{
	return CPageHelper::getMediaPermission( $pageId );
}

/**
 * Deprecated since 1.8
 * Use CPageHelper::allowViewMedia instead.
 */
function cAllowViewPageMedia($pageId)
{
	return CPageHelper::allowViewMedia( $pageId );
}

/**
 * Deprecated since 1.8
 * Use CPageHelper::allowManageVideo instead.
 */
function cAllowManagePageVideo($pageId)
{
	return CPageHelper::allowManageVideo( $pageId );
}

/**
 * Deprecated since 1.8
 * Use CPageHelper::allowManagePhoto instead.
 */
function cAllowManagePagePhoto($pageId)
{
	return CPageHelper::allowManagePhoto( $pageId );
}

/**
 * Deprecated since 1.8
 * Use CPageHelper::allowPhotoWall instead.
 */
function cAllowPhotoPageWall($pageId)
{
	return CPageHelper::allowPhotoWall( $pageId );
}