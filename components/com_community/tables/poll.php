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

require_once ( JPATH_ROOT . '/components/com_community/models/models.php');

class CTablePoll extends JTable {

    var $id = null;
    var $published = null;
    var $multiple = 0;
    var $addoption = null;
    var $permissions = null;
    var $catid = null;
    var $creator = null;
    var $title = null;
    var $enddate = null;
    var $type = 0;
    var $pageid = null;
    var $groupid = null;
    var $eventid = null;
    var $created = null;
    var $_pagination = '';

    static $members = array();

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_polls', 'id', $db);

        // set default timezone to system settings
        $systemOffset = new JDate('now', JFactory::getApplication()->get('offset'));
        $systemOffset = $systemOffset->getOffsetFromGMT(true);
        //$this->offset = $systemOffset;
    }

    /**
     * Binds an array into this object's property
     *
     * @access	public
     * @param	$data	mixed	An associative array or object
     * */
    public function bind($src, $ignore = array()) {
        $status = parent::bind($src);

        return $status;
    }

    public function load($id = null, $reset = true) {
        $status = parent::load($id);

        return $status;
    }

    public function store($updateNulls = false) {
        return parent::store();
    }

    public function getEndTime() {
        $edate = new JDate($this->enddate);
        return $edate->format('H:M');
    }

    public function _getDateTimeFormat() {

        $config = CFactory::getConfig();
        $endDate = $this->getEndDate(false);

        $format = ($config->get('eventshowampm')) ? JText::_('COM_COMMUNITY_EVENTS_TIME_FORMAT_12HR') : JText::_('COM_COMMUNITY_EVENTS_TIME_FORMAT_24HR');

        if ($endDate->format('H:M:S') == '23:59:59') {
            $format = JText::_('COM_COMMUNITY_EVENT_TIME_FORMAT_LC1');
            $allday = true;
        }

        $this->set('format', $format);

        return $format;
    }

    public function getEndDate($formatted = true, $format='') {
        if ($formatted) {
            return $this->_getEndDate($format);
        }

        $date = JDate::getInstance($this->enddate);
        return $date;
    }

    public function _getEndDate($format = '') {
        $edate = new JDate($this->enddate);
        return ($format == '') ? $edate->format('Y-m-d') : $edate->format($format);
    }

    public function getEndDateHTML() {
        $format = $this->get('format', $this->_getDateTimeFormat());
        return CTimeHelper::getFormattedTime($this->enddate, $format);
    }

    public function getCreator() {
        $user = CFactory::getUser($this->creator);
        return $user;
    }

    public function getCategoryName() {
        $category = JTable::getInstance('PollCategory', 'CTable');
        $category->load($this->catid);

        return $category->name;
    }

    public function getCreatorName() {
        $user = CFactory::getUser($this->creator);
        return $user->getDisplayName();
    }


    public function getPagination() {
        return $this->_pagination;
    }

    public function isExpired() {
        $date = new JDate($this->enddate);
        $current = JDate::getInstance();

        return $current->toUnix(true) > $date->toUnix(true);
    }

    public function isCreator($userId) {
        return ($userId == $this->creator);
    }

    public function isPublished() {
        $published = $this->published == 1 ? true : false;
        return $published;
    }
}