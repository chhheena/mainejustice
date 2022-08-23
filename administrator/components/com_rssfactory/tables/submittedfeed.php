<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class RssFactoryTableSubmittedFeed extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__rssfactory_submitted', 'id', $db);
    }

    public function check()
    {
        if (!parent::check()) {
            return false;
        }

        if (is_null($this->date)) {
            $this->date = JFactory::getDate()->toSql();
        }

        if (is_null($this->userid)) {
            $this->userid = JFactory::getUser()->id;
        }

        return true;
    }
}
