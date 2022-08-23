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

class RssFactoryTableComment extends JTable
{
    public function __construct(&$dbo)
    {
        parent::__construct('#__rssfactory_comments', 'id', $dbo);
    }

    public function check()
    {
        if (!parent::check()) {
            return false;
        }

        if (!$this->created_at) {
            $this->created_at = JFactory::getDate()->toUnix();
        }

        if (!$this->user_id) {
            $this->user_id = JFactory::getUser()->id;
        }

        return true;
    }
}
