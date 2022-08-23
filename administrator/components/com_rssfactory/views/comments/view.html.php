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

class RssFactoryBackendViewComments extends FactoryViewRss
{
    protected
        $get = array(
        'items',
        'pagination',
        'state',
        'listOrder',
        'listDirn',
        'saveOrder',
        'sortFields',
        'filters',
    ),
        $buttons = array(
        'edit',
        'publish',
        'unpublish',
        'delete',
    ),
        $html = array(
        'bootstrap.tooltip',
        'behavior.multiselect',
        'dropdown.init',
        'formbehavior.chosen/select',
    );
}
