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

class RssFactoryBackendViewConfiguration extends FactoryViewRss
{
    protected
        $buttons = array('apply', 'save', 'close'),
        $get = array('form', 'fieldsets'),
        $html = array(
        'behavior.tooltip',
        'behavior.multiselect',
        'dropdown.init',
    ),
        $permissions = array('backend.settings');

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
            $this->html[] = 'formbehavior.chosen/select';
        }
        else {
            $this->html[] = 'formbehavior.chosen/.chosen';
        }
    }

    protected function loadFieldset($fieldset)
    {
        $this->fieldset = $fieldset;

        return $this->loadTemplate('fieldset');
    }
}
