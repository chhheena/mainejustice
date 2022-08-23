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

class CTableRating extends JTable {

    var $id = null;
    var $type = null;
    var $cid = null;
    var $userid = null;
    var $rating = null;
    var $title = null;
    var $review = null;
    var $params = null;

    /**
     * Constructor
     */
    public function __construct(&$db)
    {
        parent::__construct('#__community_ratings', 'id', $db);
    }
    
    /**
     * Store the data
     */
    public function store($updateNulls = false)
    {
        $today = JDate::getInstance();
        if($this->created == ''){
            $this->created = $today->toSql();
        }
        
        return parent::store();
    }

    public function isRated($type = null, $cid = null, $userid = null)
    {
        if (!$type || !$cid || !$userid) {
            return false;
        }

        $db = $this->getDBO();

        $query = 'SELECT * FROM '
            . $db->quoteName('#__community_ratings')
            . ' WHERE ' . $db->quoteName('type') . '=' . $db->Quote($type)
            . ' AND ' . $db->quoteName('cid') . '=' . $db->Quote($cid)
            . ' AND ' . $db->quoteName('userid') . '=' . $db->Quote($userid);
        $db->setQuery($query);

        $result = $db->loadObject();

        return $result;
    }

    public function getUserRatingCount($type = null, $cid = null)
    {   
        if (!$type || !$cid) {
            return 0;
        }

        $db = JFactory::getDBO();

        $query = 'SELECT COUNT(id) FROM '.$db->quoteName('#__community_ratings');
        $query .= ' WHERE ' . $db->quoteName('type') . ' = '.$db->Quote($type);
        $query .= ' AND ' . $db->quoteName('cid') . ' = '.$db->Quote($cid);

        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getRatingResult($type = null, $cid = null)
    {   
        if (!$type || !$cid) {
            return 0;
        }

        $db = JFactory::getDBO();

        $query = 'SELECT ROUND(SUM(rating)/COUNT(id), 0) AS rating FROM '.$db->quoteName('#__community_ratings');
        $query .= ' WHERE ' . $db->quoteName('type') . ' = '.$db->Quote($type);
        $query .= ' AND ' . $db->quoteName('cid') . ' = '.$db->Quote($cid);

        $db->setQuery($query);
        
        return $db->loadResult();
    }

    public function ratingDelete($type = null, $cid = null, $userid = null)
    {
        if (!$type || !$cid || !$userid) {
            return false;
        }

        $db = $this->getDBO();

        $query = 'DELETE FROM '
            . $db->quoteName('#__community_ratings')
            . ' WHERE ' . $db->quoteName('type') . '=' . $db->Quote($type)
            . ' AND ' . $db->quoteName('cid') . '=' . $db->Quote($cid)
            . ' AND ' . $db->quoteName('userid') . '=' . $db->Quote($userid);
        
        $db->setQuery($sql);

        try {
            $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

}
