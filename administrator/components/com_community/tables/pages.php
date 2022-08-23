<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * JomSocial Table Model
 */
class CommunityTablePages extends JTable
{
	var $id = null;
    var $permissions = null;
    var $published = null;
    var $ownerid = null;
    var $categoryid = null;
    var $name = null;
    var $description = null;
    var $email = null;
    var $website = null;
    var $approvals = null;
    var $unlisted = null;
    var $created = null;
    var $avatar = null;
    var $thumb = null;
    var $discusscount = null;
    var $wallcount = null;
    var $membercount = null;
    var $params = null;
    var $_pagination = null;
    var $storage = null;
    var $cover = null;
    var $hits = 0;

	public function __construct(&$db)
	{
		parent::__construct('#__community_pages','id', $db);
	}

	public function getWallCount()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_wall') . ' '
				. 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $this->id ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'pages' ) . ' '
				. 'AND ' . $db->quoteName( 'published' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	public function isMember( $memberId , $pageId )
	{
		$db 		= JFactory::getDBO();
		$query 	= 'SELECT * FROM ' . $db->quoteName( '#__community_pages_members' ) . ' '
					. 'WHERE ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $memberId ) . ' '
					. 'AND ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $pageId );

		$db->setQuery( $query );

		$count 	= ( $db->loadResult() > 0 ) ? true : false;
		return $count;
	}

	/**
	 *  Deprecated since 2.2.x
	 *  Use CTablePage instead
	 */
	public function addMember( $data )
	{
		$db	=& $this->getDBO();

		// Test if user if already exists
		if( !$this->isMember($data->memberid, $data->pageid) )
		{
			try {
				$db->insertObject('#__community_pages_members', $data);
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return $data;
	}

	/**
	 *  Deprecated since 2.2.x
	 *  Use CTablePage instead
	 */
	public function addMembersCount( $pageId )
	{
		$db		=& $this->getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_pages' )
				. 'SET ' . $db->quoteName( 'membercount' ) . '= (' . $db->quoteName('membercount'). ' +1) '
				. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote( $pageId );
		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
	}

	public function getMembersCount()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_pages_members') . ' '
				. 'WHERE ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $this->id )
				. 'AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	/**
	 * Return the full URL path for the specific image
	 *
	 * @param	string	$type	The type of avatar to look for 'thumb' or 'avatar'. Deprecated since 1.8
	 * @return string	The avatar's URI
	 **/
	public function getAvatar( $type = 'thumb' )
	{
		if( $type == 'thumb' )
		{
			return $this->getThumbAvatar();
		}

		// Get the avatar path. Some maintance/cleaning work: We no longer store
		// the default avatar in db. If the default avatar is found, we reset it
		// to empty. In next release, we'll rewrite this portion accordingly.
		// We allow the default avatar to be template specific.
		if ($this->avatar == 'components/com_community/assets/page.jpg')
		{
			$this->avatar = '';
			$this->store();
		}
		//CFactory::load('helpers', 'url');
		$avatar	= CUrlHelper::avatarURI($this->avatar, 'pageAvatar.png');

		return $avatar;
	}

	public function getThumbAvatar()
	{
		if ($this->thumb == 'components/com_community/assets/page_thumb.jpg')
		{
			$this->thumb = '';
			$this->store();
		}
		//CFactory::load('helpers', 'url');
		$thumb	= CUrlHelper::avatarURI($this->thumb, 'pageThumbAvatar.png');

		return $thumb;
	}


	/**
	* Update stats in admin section
	*/
	public function updateStats()
	{
		if( $this->id != 0 )
		{
			$db	= JFactory::getDBO();

			// @rule: Update the members count each time stored is executed.
			$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( '#__community_pages_members' ) . ' AS a '
					. 'JOIN '. $db->quoteName( '#__users' ). ' AS b ON a.'.$db->quoteName('memberid').'=b.'.$db->quoteName('id')
					. 'AND b.'.$db->quoteName('block').'=0 '
					. 'WHERE ' . $db->quoteName('pageid') .'=' . $db->Quote( $this->id ) . ' '
					. 'AND ' . $db->quoteName('approved'). '=' . $db->Quote( '1' ) . ' '
					. 'AND permissions!=' . $db->Quote(COMMUNITY_PAGE_BANNED);

			$db->setQuery( $query );
			$this->membercount	= $db->loadResult();

			// @rule: Update the wall count each time stored is executed.
			$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( '#__community_activities' ) . ' '
					. 'WHERE ' . $db->quoteName('cid'). '=' . $db->Quote( $this->id ) . ' '
					. 'AND '. $db->quoteName('app') .'=' . $db->Quote( 'pages.wall' );

			$db->setQuery( $query );
			$this->wallcount	= $db->loadResult();
		}
	}

}