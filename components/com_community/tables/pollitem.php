<?php
/**
 * @copyright (C) 2016 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CTablePollItem extends JTable
{
    public $id;
    public $poll_id;
    public $value;
    public $count;

    /**
     * Constructor
     */
    public function __construct( $db )
    {
        parent::__construct( '#__community_polls_items', 'id', $db );
    }

    public function store($updateNulls = false) {
        return parent::store();
    }

    public function updateVoteCounter($pollId)
    {
        $db = JFactory::getDbo();

        $pollModel = CFactory::getModel('polls');
        $pollItems = $pollModel->getPollItems($pollId);

        foreach ($pollItems as $item) {
            $query = "SELECT id FROM " .$db->quoteName('#__community_polls_users')
                . ' WHERE ' . $db->quoteName('poll_id') . '=' . $db->Quote($pollId)
                . ' AND ' . $db->quoteName('poll_itemid') . '=' . $db->Quote($item->id);

            $db->setQuery($query);
            $db->execute();

            $count = $db->getNumRows();

            $query = 'UPDATE ' . $db->quoteName('#__community_polls_items')
                . ' SET ' . $db->quoteName('count') . '=' . $db->Quote($count)
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($item->id);

            $db->setQuery( $query );
            $db->execute();
        }
        
        return true;
    }
}
