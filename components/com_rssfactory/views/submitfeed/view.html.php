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

class RssFactoryFrontendViewSubmitFeed extends FactoryViewRss
{
    protected
        $option = 'com_rssfactory',
        $get = array('item', 'form', 'state'),
        $html = array('behavior.tooltip', 'behavior.formvalidation'),
        $permissions = array('frontend.submitfeed');
}
