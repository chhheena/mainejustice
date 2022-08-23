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

JFormHelper::loadFieldType('Radio');

class JFormFieldFactoryBoolean extends JFormFieldRadio
{
    protected $type = 'FactoryBoolean';

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $element['class'] = 'switcher btn-group';
        $element['filter'] = 'integer';

        return parent::setup($element, $value, $group);
    }

    protected function getOptions()
    {
        $options = parent::getOptions();

        if ($options) {
            return $options;
        }

        $options = array(
            (object)array('value' => 0, 'text' => JText::_('JNO')),
            (object)array('value' => 1, 'text' => JText::_('JYES')),
        );

        if ('true' === (string)$this->element['global']) {
            array_unshift($options, (object)array(
                'value' => -1,
                'text'  => JText::_('JGLOBAL_USE_GLOBAL'),
            ));
        }

        return $options;
    }
}
