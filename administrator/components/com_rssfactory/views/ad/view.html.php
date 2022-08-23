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

class RssFactoryBackendViewAd extends FactoryViewRss
{
    protected
        $get = array(
        'item', 'form', 'state',
    ),
        $buttons = array(
        'apply', 'save', 'close',
    ),
        $html = array(
        'behavior.tooltip',
        'bootstrap.tooltip',
        'behavior.multiselect',
        'dropdown.init',
        'behavior.formvalidation',
        'formbehavior.chosen/select',
    );

    protected function loadFieldset($fieldset)
    {
        $this->fieldset = $fieldset;

        return $this->loadTemplate('fieldset');
    }
}
