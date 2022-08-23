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

class CTablePageInvite extends JTable
{
	var $pageid	= null;
	var $userid		= null;
	var $creator	= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_pages_invite' , 'pageid' , $db );
	}

	public function isOwner()
	{
		$my		= CFactory::getUser();

		return $my->id == $this->userid;
	}

	/*public function load( $keys=null, $reset=true )
	{
		$this->userid = $keys['userid'];
		$this->pageid = $keys['pageid'];
	}*/

	public function exists()
	{
		$db		=  $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_pages_invite' )
				. 'WHERE ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $this->pageid ) . ' '
				. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );

		$db->setQuery( $query );

		try {
			$return = ($db->loadResult() >= 1) ? true : false;
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		return $return;
	}

	public function store( $updateNulls = false )
	{
		$db		=  $this->getDBO();

		if( !$this->exists() )
		{
 			$data			= new stdClass();

 			foreach( get_object_vars($this) as $property => $value )
 			{
 				// We dont want to set private properties
				if( CStringHelper::strpos( CStringHelper::strtolower($property) , '_') === false )
				{
					$data->$property	= $value;
				}
			}
			return $db->insertObject( '#__community_pages_invite' , $data );
		}
		else
		{
			$query	= 'UPDATE ' . $db->quoteName( '#__community_pages_invite' ) . ' '
					. 'SET ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $this->pageid ) . ', '
					. $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid ) . ' ,'
					. $db->quoteName( 'creator' ) . '=' . $db->Quote( $this->creator ) . ' '
					. 'WHERE ' . $db->quoteName( 'pageid' ) . '=' . $db->Quote( $this->pageid ) . ' '
					. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );
			$db->setQuery( $query );
			try {
				$db->execute();
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				return false;
			}

			return true;
		}
	}

	public function delete($pk = null)
	{
		$db		=  $this->getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( $this->_tbl ) . ' WHERE '
				. $db->quoteName('pageid'). '=' . $db->Quote( $this->pageid ) . ' AND '
				. $db->quoteName('userid') .'=' . $db->Quote( $this->userid );
		$db->setQuery( $query );
		return $db->execute();
	}
}