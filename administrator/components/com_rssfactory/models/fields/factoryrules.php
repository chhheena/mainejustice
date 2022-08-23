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

JFormHelper::loadFieldType('Rules');

class JFormFieldFactoryRules extends JFormFieldRules
{
    public $type = 'FactoryRules';

//    public function setup(SimpleXMLElement $element, $value, $group = null)
//    {
//        $prefix = (string)$element->attributes()->prefixHelpers;
//        foreach ($element->xpath('//action') as $action) {
//            $name = $action->attributes()->name;
//            $title = call_user_func_array(array($prefix . 'FactoryText', '_'), array('rules_' . str_replace('.', '_', $name) . '_label'));
//            $description = call_user_func_array(array($prefix . 'FactoryText', '_'), array('rules_' . str_replace('.', '_', $name) . '_desc'));
//
//            @$action->addAttribute('title', $title);
//            @$action->addAttribute('description', $description);
//        }
//
//        return parent::setup($element, $value, $group);
//    }

    protected function getInput()
    {
        $input = parent::getInput();

        $input = str_replace('onchange="sendPermissions.call(this, event)"', '', $input);

        return $input;
    }
}
