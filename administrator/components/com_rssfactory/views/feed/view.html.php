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

class RssFactoryBackendViewFeed extends FactoryViewRss
{
    protected
        $get = array(
        'item', 'form', 'state',
    ),
        $buttons = array(
        'apply',
        'save',
        'close',
    ),
        $html = array(
        'behavior.tooltip',
        'bootstrap.tooltip',
        'behavior.multiselect',
        'dropdown.init',
        'behavior.formvalidation',
    ),
        $css = array('buttons');

    public function display($tpl = null)
    {
        JHtml::_('jquery.ui', array('core', 'sortable'));

        if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
            $this->html[] = 'formbehavior.chosen/select';
        }

        parent::display($tpl);
    }

    protected function loadFieldset($fieldset)
    {
        $this->fieldset = $fieldset;

        return $this->loadTemplate('fieldset');
    }

    protected function addToolbar()
    {
        if ($this->item->id) {
            $this->buttons[] = array('refresh', 'feeds_list_refresh', 'loop', false);
        }

        parent::addToolbar();
    }
}
