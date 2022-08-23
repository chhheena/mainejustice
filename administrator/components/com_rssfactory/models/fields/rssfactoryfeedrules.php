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

class JFormFieldRssFactoryFeedRules extends JFormField
{
    protected $type = 'RssFactoryFeedRules';

    protected function getLabel()
    {
        if ('false' == $this->element['hasLabel']) {
            return '';
        }

        return parent::getLabel();
    }

    protected function getInput()
    {
        JLoader::register('RssFactoryRule', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rules/rule.php');
        JLoader::register('JHtmlRssFactoryRules', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rules/html.php');

        $html = array();

        $html[] = '<div>';
        $html[] = JHtmlSelect::genericlist(JHtml::_('RssFactoryRules.options'), 'rules', 'class="custom-select"');
        $html[] = '<a href="#" class="btn btn-small btn-success button-add-rule" style="vertical-align: top;"><i class="icon-new"></i>&nbsp;' . FactoryTextRss::_('rule_add_new') . '</a>';
        $html[] = '</div>';

        $html[] = '<div class="rules">';

        $last = 0;
        if ($this->value) {
            foreach ($this->value as $value) {
                $rule = RssFactoryRule::getInstance($value['type']);
                $html[] = $rule->getTemplate($value['order'], $value, $this->getName($this->fieldname));

                $last = $value['order'];
            }
        }

        $html[] = '</div>';

        $html[] = '<div class="rules-templates" data-last="' . $last . '" ' . JHtml::_('RssFactoryRules.templates') . '></div>';

        return implode("\n", $html);
    }
}
