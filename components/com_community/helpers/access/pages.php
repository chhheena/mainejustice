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

Class CPagesAccess implements CAccessInterface
{

	/**
	 * Method to check if a user is authorised to perform an action in this class
	 *
	 * @param	integer	$userId	Id of the user for which to check authorisation.
	 * @param	string	$action	The name of the action to authorise.
	 * @param	mixed	$asset	Name of the asset as a string.
	 *
	 * @return	boolean	True if authorised.
	 * @since	Jomsocial 2.4
	 */
	static public function authorise()
	{
		$args      = func_get_args();
		$assetName = array_shift ( $args );

		if (method_exists(__CLASS__,$assetName)) {
			return call_user_func_array(array(__CLASS__, $assetName), $args);
		} else {
			return null;
		}
	}


	/**
	 *
	 * @since 2.4
	 * @param type $userId
	 * @param type $pageId
	 * @param type $page
	 */
	static public function pagesStreamView($userId, $pageId, $page)
	{
		return $page->isMember( $userId );
	}

	/*
	 * This function will get the permission to invite list
	 * @param type $userId
	 * @return : bool
	 */
    static public function pagesInvitelistView($userId)
    {
		$config = CFactory::getConfig();

		if( !$config->get('enablepages') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false ;
		} else {
			return true;
		}
    }


	/*
	 * This function will get the permission to invite user in a page
	 * @param type $userId
	 * @param type $assetId
	 * @param type $page object
	 * @return : bool
	 */
    static public function pagesInviteView($userId, $pageId, $page)
    {
		$config = CFactory::getConfig();

        if (!$page->isMember($userId) && !COwnerHelper::isCommunityAdmin()) {
            return false;
        } else {
          return true;
        }
    }

	/*
	 * Return true if can report on page
	 * @param type $userId
	 * @return : bool
	 */
	static public function pagesReportView($userId)
	{
		$config = CFactory::getConfig();

		if( !$config->get('enablereporting') || ( ( $userId == 0 ) && ( !$config->get('enableguestreporting') ) ) ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can report on page
	 * @param type $userId
	 * @return : bool
	 */
	static public function pagesSearchView($userId=0)
	{
		if(!$userId) {
			$my = CFactory::getUser();
			$userId = $my->id;
		}

		$config    = CFactory::getConfig();

		if( !$config->get('enablepages') ){
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else if( $userId == 0 && !$config->get('enableguestsearchpages') ) {
			CAccess::setError('blockUnregister');
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can view page
	 * @return : bool
	 */
	static public function pagesListView($userId)
	{
		$config = CFactory::getConfig();

		if( !$config->get('enablepages') ) {
			return false;
		} else {
			return true;
		}
	}

	static public function pagesMyView($userId)
	{
		$config = CFactory::getConfig();
        $requestUser = CFactory::getRequestUser();

		if ($userId == 0 && $requestUser->_cparams->get('privacyPagesView') > 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else if( !$config->get('enablepages') ) {
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page
	 * @param type $userId
	 * @return : bool
	 */
	static public function pagesAdd($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();


		return true;
		if ($userId == 0){
			CAccess::setError('blockUnregister');
			return false;
		} else if (!$config->get('enablepages')) {
			CACCESS::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else if( !$config->get('createpages')  ||  !( COwnerHelper::isCommunityAdmin() || (COwnerHelper::isRegisteredUser() && $my->canCreatePages())) ) {
			CACCESS::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE_CREATE_MESSAGE'));
			return false;
		} else if(CLimitsHelper::exceededPageCreation($userId)) {
			$pageLimit	   = $config->get('pagecreatelimit');
			CACCESS::setError(JText::sprintf('COM_COMMUNITY_PAGES_LIMIT' , $pageLimit));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page
	 * @return : bool
	 */
	static public function pagesEdit($userId, $pageId, $page)
	{
		$document	= JFactory::getDocument();
		$viewType	= $document->getType();
        $jinput = JFactory::getApplication()->input;
		$viewName	= $jinput->get( 'view' );
		$view		= CFactory::getView($viewName, '', $viewType);

		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
        // ACL check
		} else if( !$page->isAdmin($userId) && !CFactory::getUser()->authorise('community.pageedit', 'com_community')) {
			CAccess::setError($view->noAccess());
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can delete page
	 * @param type $userId
	 * @param type $pageId
	 * @param type $page object
	 * @return : bool
	 */
	static public function pagesDelete($userId, $pageId, $page)
	{
		if (!CFactory::getUser()->authorise('community.pagedelete', 'com_community') && !($userId == $page->ownerid)) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can join page
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesJoin($userId, $pageId)
	{
		if( $userId == 0 ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can leave page
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesLeave($userId, $pageId)
	{
		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can leave page
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesAvatarUpload($userId, $pageId, $page)
	{
		$document	= JFactory::getDocument();
		$viewType	= $document->getType();
        $jinput = JFactory::getApplication()->input;
		$viewName	= $jinput->get( 'view' );
		$view		= CFactory::getView($viewName, '', $viewType);

		if( $userId == 0 ) {
			CAccess::setError('blockUnregister');
			return false;
		} else if( !$page->isAdmin($userId) && !COwnerHelper::isCommunityAdmin() ) {
			CAccess::setError($view->noAccess());
			return false;
		} else {
			return true;
		}

	}

	/*
	 * Return true if can delete page discussion
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesDiscussionDelete($userId, $pageId)
	{  
        // Access check: ACL
        if (!CFactory::getUser()->authorise('community.postcommentcreate', 'com_community')) {
            return false;
        } else if (CFactory::getUser()->authorise('community.postcommentdelete', 'com_community')) {
            return true;
        }

		$page	= CFactory::getModel( 'pages' );

		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ( !COwnerHelper::isCommunityAdmin() && !$page->isAdmin( $userId , $pageId ) ) {
			CACCESS::setError(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_REMOVE_WALL'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can approve page member
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesMemberApprove($userId, $pageId)
	{
		$page = CFactory::getModel( 'pages' );

		if( !$page->isAdmin( $userId , $pageId ) && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page wall.
	 * @param type $userId
	 * @param type $pageId
	 * @param type $wall object
	 * @return : bool
	 */
	static public function pagesWallEdit($userId, $pageId, $wall)
	{
		$page = CFactory::getModel( 'Pages' );

		if( $page->isAdmin( $userId , $pageId ) || COwnerHelper::isCommunityAdmin() || $userId == $wall->post_by ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Return true if can edit page discussion
	 * @param type $userId
	 * @param type $pageId
	 * @param type $wall object
	 * @return : bool
	 */
	static public function pagesDiscussionEdit($userId, $pageId, $wall)
	{  
        // Access check: ACL
        if (!CFactory::getUser()->authorise('community.postcommentcreate', 'com_community')) {
            return false;
        }

		$page = CFactory::getModel( 'Pages' );

		if( $page->isAdmin( $userId , $pageId ) || CFactory::getUser()->authorise('community.postcommentedit', 'com_community') || $userId == $wall->post_by ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Return true if can remove member
	 * @param type $userId
	 * @param type $memberId
	 * @param type $page object
	 * @return : bool
	 */
	static public function pagesMemberRemove($userId, $memberId, $page)
	{  
		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ($page->ownerid == $memberId) {
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_MEMBERS_DELETE_DENIED'));
			return false;
		} else if (!$page->isAdmin($userId) && !COwnerHelper::isCommunityAdmin()) {
            CAccess::setError(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
            return false;
        } else {
			return true;
		}
	}

	/*
	 * Return true if can remove page wall.
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesWallDelete($userId, $pageId)
	{
		$pageModel	= CFactory::getModel( 'pages' );

		if (!COwnerHelper::isRegisteredUser()) {
			CAccess::setError('blockUnregister');
			return false;
		} else if ( !COwnerHelper::isCommunityAdmin() && !$pageModel->isAdmin( $userId , $pageId ) ) {
			CAccess::setError(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_REMOVE_WALL'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page admin
	 * @param type $userId
	 * @param type $pageId
	 * @param type $page
	 * @return : bool
	 */
	static public function pagesAdminEdit($userId, $pageId, $page)
	{
		if( $page->ownerid != $userId && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page admin
	 * @param type $userId
	 * @param type $pageId
	 * @param type $page
	 * @return : bool
	 */
	static public function pagesWallSave($userId, $pageId, $page)
	{
		$config	= CFactory::getConfig();

		if( $config->get('lockpagewalls') && !$page->isMember( $userId ) ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page admin
	 * @param type $userId
	 * @param type $pageId
	 * @param type $page
	 * @return : bool
	 */
	static public function pagesMemberBanUpdate($userId, $pageId, $page)
	{
		if( $page->ownerid != $userId && !COwnerHelper::isCommunityAdmin() ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can edit page admin
	 * @param type $userId
	 * @param type $pageId
	 * @return : bool
	 */
	static public function pagesMemberView($userId, $pageId)
	{
		$config	= CFactory::getConfig();
		if( !$config->get('enablepages') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

    static public function pagesReviewView($userId, $pageId)
    {
        $config = CFactory::getConfig();
        if( !$config->get('enablepages') )
        {
            CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
            return false;
        } else {
            return true;
        }
    }

	/**
	 * @param $userId
	 * @param $page
	 * @return bool
	 */
	static public function pagesAnnouncementCreate($userId, $pageId){
		$page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

		//only admins can create
		if(COwnerHelper::isCommunityAdmin() || $page->isAdmin($userId)){
			return true;
		}

		return false;
	}

	/*
	 * Return true if can view bulletins
	 * @param type $userId
	 * @return : bool
	 */
	static public function pagesBulletinsView($userId)
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enablepages') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can view bulletin
	 * @param type $userId
	 * @param type $bullentinId
	 * @return : bool
	 */
	static public function pagesBulletinView($userId, $bullentinId)
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enablepages') )
		{
			CAccess::setError(JText::_('COM_COMMUNITY_PAGES_DISABLE'));
			return false;
		} else {
			return true;
		}
	}

    /*
     * Return true if can create a discussion
     * @param type $userId
     * @param type $pageId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 not logged in
     * 1 super admin
     * 1 page owner
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function pagesDiscussionsCreate($userId, $pageId)
    {   
        // Access check: ACL
        if (!CFactory::getUser()->authorise('community.postcommentcreate', 'com_community')) {
            return false;
        }

        $config	= CFactory::getConfig();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $pageModel = CFactory::getModel('pages');

        // FALSE globally disabled
        if(!$config->get('creatediscussion')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($page->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE not member
        if(!$page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($page->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($pageModel->isWaitingAuthorization($userId, $pageId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    /*
     * Return true if can create a video
     * @param type $userId
     * @param type $pageId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 disabled for page
     * 0 not logged in
     * 1 super admin
     * 1 page owner
     * 0 disabled for members
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function pagesVideosCreate($userId, $pageId)
    {
        $config	= CFactory::getConfig();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $params = $page->getParams();

        $pageModel = CFactory::getModel('pages');

        // FALSE globally disabled
        if(!$config->get('pagevideos')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE page videos disabled
        if($params->get('videopermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($page->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('videopermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($page->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($pageModel->isWaitingAuthorization($userId, $pageId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    /*
     * Return true if can create an event
     * @param type $userId
     * @param type $pageId
     * @return : bool
     *
     * Test cases (cascading - the next rule is only checked if the previous didn't kick in):
     *
     * 0 globally disabled
     * 0 disabled for page
     * 0 not logged in
     * 1 super admin
     * 1 page owner
     * 0 disabled for members
     * 0 not member
     * 0 member, banned
     * 0 member, awaiting approval
     * 1 member
     * 0 default (should not be the case)
     */
    static public function pagesEventsCreate($userId, $pageId)
    {
        $config	= CFactory::getConfig();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $params = $page->getParams();

        $pageModel = CFactory::getModel('pages');

        // FALSE globally disabled
        if(!$config->get('page_events')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE page events disabled
        if($params->get('eventpermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($page->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('eventpermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($page->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($pageModel->isWaitingAuthorization($userId, $pageId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    static public function pagesPhotosCreate($userId, $pageId)
    {
        $config	= CFactory::getConfig();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $params = $page->getParams();

        $pageModel = CFactory::getModel('pages');

        // FALSE globally disabled
        if(!$config->get('pagephotos') || !CFactory::getUser()->authorise('community.photocreate', 'com_community')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE page photos disabled
        if($params->get('photopermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId) || $page->isAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($page->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('photopermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($page->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($pageModel->isWaitingAuthorization($userId, $pageId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

    static public function pagesPollsCreate($userId, $pageId)
    {
        $config = CFactory::getConfig();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $params = $page->getParams();

        $pageModel = CFactory::getModel('pages');

        // FALSE globally disabled
        if(!$config->get('page_polls') || !CFactory::getUser()->authorise('community.pollcreate', 'com_community')) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE page photos disabled
        if($params->get('pollspermission') == -1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not logged in
        if(!$userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // TRUE Super Admin
        if(COwnerHelper::isCommunityAdmin($userId) || $page->isAdmin($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // TRUE owner
        if($page->ownerid == $userId) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // FALSE only admins can post
        if($params->get('pollspermission') == 1) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE not member
        if(!$page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but banned
        if($page->isBanned($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return false;
        }

        // FALSE member, but waiting approval
        if($pageModel->isWaitingAuthorization($userId, $pageId)) {
            echo "<!-- " . __FUNCTION__ . __LINE__ . "-->";
            return false;
        }

        // TRUE member
        if($page->isMember($userId)) {
            echo "<!-- ".__FUNCTION__.__LINE__."-->";
            return true;
        }

        // default (shouldn't really kick in)
        echo "<!-- ".__FUNCTION__.__LINE__."-->";
        return false;
    }

	static public function pagesCreate($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();

        // ACL check
        if (!CFactory::getUser()->authorise('community.pagecreate', 'com_community')) {
            return false;
        }

		//admin can always create page
		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		return $config->get('createpages') && (COwnerHelper::isRegisteredUser() && $my->canCreatePages() );
	}

}