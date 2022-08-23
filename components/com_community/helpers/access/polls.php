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

Class CPollsAccess implements CAccessInterface
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

	static public function pollsListView($userId)
	{
		$config = CFactory::getConfig();

		if( !$config->get('enablepolls') ) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Return true if can add poll
	 * @param type $userId
	 * @return : bool
	 */
	static public function pollsAdd($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();
        
		if ($userId == 0){
			CAccess::setError('blockUnregister');
			return false;
		} else if (!$config->get('enablepolls')) {
			CACCESS::setError(JText::_('COM_COMMUNITY_POLLS_DISABLE'));
			return false;
		} else if (!COwnerHelper::isCommunityAdmin() && !$config->get('createpolls')) {
			CACCESS::setError(JText::_('COM_COMMUNITY_POLLS_DISABLE_CREATE_MESSAGE'));
			return false;
		} else if (CLimitsHelper::exceededPollCreation($userId)) {
			$pollLimit = $config->get('pollcreatelimit');
			CACCESS::setError(JText::sprintf('COM_COMMUNITY_POLLS_LIMIT', $pollLimit));
			return false;
		} else {
			return true;
		}
	}

	static public function pollsCreate($userId)
	{
		$config = CFactory::getConfig();
		$my		= CFactory::getUser();

        // ACL check
        if (!CFactory::getUser()->authorise('community.pollcreate', 'com_community')) {
            return false;
        }

		//admin can always create group
		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		return $config->get('createpolls') && (COwnerHelper::isRegisteredUser() && $my->canCreatePolls() );
	}

	static public function pollsEdit($userId, $pollId, $poll)
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
		} else if( !$poll->isCreator($userId) && !CFactory::getUser()->authorise('community.polledit', 'com_community')) {
			return false;
		} else {
			return true;
		}
	}

	static public function pollsDelete($userId, $pollId, $poll)
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
		} else if( !$poll->isCreator($userId) && !CFactory::getUser()->authorise('community.polldelete', 'com_community')) {
			return false;
		} else {
			return true;
		}
	}

	static public function pollsSearchView($userId = 0)
	{
		if (!$userId) {
			$my = CFactory::getUser();
			$userId = $my->id;
		}

		$config = CFactory::getConfig();

		if (!$config->get('enablepolls')) {
			CAccess::setError(JText::_('COM_COMMUNITY_POLLS_DISABLE'));
			return false;
		} else if ($userId == 0 && !$config->get('enableguestsearchpolls')) {
			CAccess::setError('blockUnregister');
			return false;
		} else {
			return true;
		}
	}

	static public function pollsMyView($userId)
	{
		$config = CFactory::getConfig();
        $requestUser = CFactory::getRequestUser();

		if( !$config->get('enablepolls') ) {
			CAccess::setError(JText::_('COM_COMMUNITY_POLLS_DISABLE'));
			return false;
		} else {
			return true;
		}
	}
}