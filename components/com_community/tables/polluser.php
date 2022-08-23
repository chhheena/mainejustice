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

class CTablePollUser extends JTable
{
    public $id;
    public $poll_id;
    public $poll_itemid;
    public $user_id;
    public $state;

    /**
     * Constructor
     */
    public function __construct( $db )
    {
        parent::__construct( '#__community_polls_users', 'id', $db );
    }

    public function store($updateNulls = false) {
        return parent::store();
    }

}
