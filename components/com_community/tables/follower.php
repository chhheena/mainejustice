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

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_community/tables');

class CTableFollower extends JTable
{
	var $id = null;
	var $user_id = null;
	var $following = null;
	var $params = null;
	var $created = null;


	public function __construct( &$db )
	{
		parent::__construct( '#__community_follower', 'id', $db );
	}


	public function getFollowerCount($user_id = null)
	{	
		if (!$user_id) {
			$user_id = $this->user_id;
		}

		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(id) FROM '.$db->quoteName( '#__community_follower').' AS ' . $db->quoteName('a');
		$query .= ' WHERE ' . $db->quoteName('a') . '.' . $db->quoteName('following') . ' = '.$db->Quote($user_id);

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	public function getFollowingCount($user_id = null)
	{	
		if (!$user_id) {
			$user_id = $this->user_id;
		}

		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(id) FROM '.$db->quoteName( '#__community_follower').' AS ' . $db->quoteName('a');
		$query .= ' WHERE ' . $db->quoteName('a') . '.' . $db->quoteName('user_id') . ' = '.$db->Quote($user_id);

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}
}
?>