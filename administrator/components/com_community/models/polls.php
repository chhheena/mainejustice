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

jimport( 'joomla.application.component.model' );

class CommunityAdminModelPolls extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object
	 **/
	var $_params;

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_total;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_data;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$mainframe	= JFactory::getApplication();
		$jinput     = $mainframe->input;

		// Call the parents constructor
		parent::__construct();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->get('list_limit'), 'int' );
		//$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.limitstart', 'limitstart', 0, 'int' );
		$limitstart = $jinput->request->get('limitstart', 0);
		
		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to return the total number of rows
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
	}

	/**
	 * Build the SQL query string
	 *
	 * @access	private
	 * @return	string	SQL Query string
	 */
	public function _buildQuery()
	{
		$db		= JFactory::getDBO();
        $mainframe	= JFactory::getApplication();
        $jinput = $mainframe->input;
		$category	= $jinput->getInt( 'category' , 0 );
		$status 	= $jinput->getInt( 'status' , 2 );
		$condition	= '';
		$ordering = $mainframe->getUserStateFromRequest('com_community.polls.filter_order', 'filter_order', 'a.created', 'cmd');
		$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.polls.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$orderBy = ' ORDER BY '. $ordering .' '. $orderDirection;
		$search	= $jinput->get('search','','STRING');

		if (!empty($search)) {
			$condition .= ' AND (a.title LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
				. 'OR a.id LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
				. 'OR username LIKE ' . $db->Quote( '%' . $search . '%' ) . ')';
		}

		if ($category != 0) {
			$condition	.= ' AND a.catid=' . $db->Quote( $category );
		}

		if ($status != 2) {
			$condition .= ' AND a.published='. $db->Quote( $status );
		}

		$query = 'SELECT a.*, c.name AS username FROM ' . $db->quoteName( '#__community_polls' ) . ' AS a '
			. 'LEFT JOIN ' . $db->quoteName( '#__users') . ' AS c '
			. 'ON a.creator=c.id '
			. 'WHERE 1'
			. $condition
			. ' GROUP BY a.id'
			. $orderBy;

		return $query;
	}

	/**
	 * Returns the Polls
	 *
	 * @return Array of polss object
	 **/
	public function getPolls()
	{
		if(empty($this->_data)) {
			$query = $this->_buildQuery( );
			$this->_data = $this->_getList( $this->_buildQuery() , $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_data;
	}

	public function getAllPolls($orderBy = '')
	{
		$db	= JFactory::getDBO();

        $sortQuery = '';
        
        if($orderBy){
            $sortQuery = ' ORDER BY '.$db->quoteName($orderBy) . ' DESC';
        }

		$query	= "SELECT * FROM " . $db->quoteName( '#__community_polls').$sortQuery;

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns the Groups Categories list
	 *
	 * @return Array An array of group category objects
	 **/
	public function &getCategories()
	{
		$mainframe	= JFactory::getApplication();

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_polls_category');
		$db->setQuery( $query );
		$categories	= $db->loadObjectList();

		return $categories;
	}

	public function isLatestTable()
	{
		$fields	= $this->_getFields();

		if(!array_key_exists( 'membercount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'wallcount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'discusscount' , $fields ) )
		{
			return false;
		}

		return true;
	}

	public function _getFields( $table = '#__community_polls' )
	{
		$result	= array();
		$db		= JFactory::getDBO();

		$query	= 'SHOW FIELDS FROM ' . $db->quoteName( $table );

		$db->setQuery( $query );

		$fields	= $db->loadObjectList();

		foreach( $fields as $field )
		{
			$result[ $field->Field ]	= preg_replace( '/[(0-9)]/' , '' , $field->Type );
		}

		return $result;
	}

	public function getPendingPolls()
	{
		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_polls')
				.' WHERE '.$db->quoteName('published') .' = '.$db->quote(0);

		$db->setQuery($query);

		return $db->loadResult();
	}
}