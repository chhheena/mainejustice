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

abstract class RssFactoryRule
{
    protected $type;
    protected $label;

    public function __construct()
    {
    }

    public function getTemplate($order = '__i__', $data = array(), $name = 'jform[params][i2c_rules]')
    {
        // Initialise variables.
        $html = array();
        $control = $name . '[' . $order . ']';
        $collapsed = isset($data['collapse']) ? $data['collapse'] : 0;
        $enabled = isset($data['enabled']) ? $data['enabled'] : 1;

        // Get form.
        $form = $this->getForm($this->getType(), $order, $control, $data);

        $html[] = '<fieldset class="fieldset-rule">';
        $html[] = '<legend><i class="icon-move" style="cursor: pointer;"></i>&nbsp;' . $this->getLabel() . '</legend>';

        $html[] = '<div class="params" style="display: ' . ($collapsed ? 'none' : '') . '">';

        if ($form) {
            foreach ($form->getFieldset('params') as $field) {
                $html[] = '<div class="control-group">';
                $html[] = '<div class="control-label">' . $field->label . '</div>';
                $html[] = '<div class="controls">' . $field->input . '</div>';
                $html[] = '</div>';
            }
        }

        $html[] = '<input type="hidden" name="' . $control . '[type]" value="' . $this->getType() . '" />';
        $html[] = '<input type="hidden" name="' . $control . '[order]" value="' . $order . '" />';
        $html[] = '<input type="hidden" name="' . $control . '[collapse]" value="' . $collapsed . '" />';
        $html[] = '</div>';

        $html[] = '<div style="background-color: #cccccc; padding: 10px 10px 5px 10px; border-radius: 5px;">';
        $html[] = JHtml::_(
            'select.genericlist',
            array(0 => FactoryTextRss::_('rule_disabled'), 1 => FactoryTextRss::_('rule_enabled')),
            $control . '[enabled]',
            'style="width: 100px;"',
            'value',
            'text',
            $enabled
        );

        // Add collapse button.
        $icon = $collapsed ? 'down' : 'up';
        $html[] = '<a href="#" class="btn btn-small btn-toggle-rule" style="vertical-align:top;"><i class="icon-arrow-' . $icon . '"></i>&nbsp;' . FactoryTextRss::_('rule_toggle') . '</a>';

        $html[] = '&nbsp;';
        $html[] = '<a href="#" class="btn btn-small button-remove-rule" style="vertical-align:top;"><i class="icon-delete"></i>&nbsp;' . FactoryTextRss::_('rule_remove') . '</a>';
        $html[] = '</div>';

        $html[] = '</fieldset>';

        return implode('', $html);
    }

    public static function getInstance($type)
    {
        $class = 'RssFactoryRule' . ucfirst($type);

        if (!class_exists($class)) {
            $file = __DIR__ . '/' . $type . '/' . $type . '.php';

            if (!file_exists($file)) {
                throw new Exception(FactoryTextRss::sprintf('rule_not_found', $type));
            }

            require_once $file;
        }

        return new $class;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getType()
    {
        if (is_null($this->type)) {
            $this->type = strtolower(str_replace('RssFactoryRule', '', get_class($this)));
        }

        return $this->type;
    }

    public function getParsedOutput($params, $page, &$content, $debug)
    {
        $params = new JRegistry($params);
        $parsed = $this->parse($params, $page, $content, $debug);

        if (!$debug) {
            return $parsed;
        }

        $html = array();

        $html[] = '<div class="rule-debug-title">' . $this->getLabel() . '</div>';
        $html[] = '<div class="rule-debug">';
        $html[] = $parsed;
        $html[] = '</div>';

        return implode("\n", $html);
    }

    public function parse($params, $page, &$content, $debug)
    {
        return '';
    }

    protected function getForm($type, $order, $control, $data)
    {
        $path = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rules/' . $type;
        JFormHelper::addFormPath($path);

        if (!file_exists($path . '/' . $type . '.xml')) {
            return false;
        }

        $form = JForm::getInstance($type . '_' . $order, $type, array('control' => $control));
        $form->bind($data);

        $this->setLabelAndDescription($form);

        return $form;
    }

    protected function setLabelAndDescription($form)
    {
        foreach ($form->getFieldset('params') as $field) {
            // Set label.
            $label = $form->getFieldAttribute($field->fieldname, 'label', null, $field->group);
            if (is_null($label)) {
                $label = FactoryTextRss::_('rule_' . $this->getType() . '_' . str_replace('.', '_', $field->group) . '_' . $field->fieldname . '_label');
                $form->setFieldAttribute($field->fieldname, 'label', $label, $field->group);
            }

            // Set description.
            $desc = $form->getFieldAttribute($field->fieldname, 'description', null, $field->group);
            if (is_null($desc)) {
                $desc = FactoryTextRss::_('rule_' . $this->getType() . '_' . str_replace('.', '_', $field->group) . '_' . $field->fieldname . '_desc');
                $form->setFieldAttribute($field->fieldname, 'description', $desc, $field->group);
            }
        }

        return true;
    }

    protected function stripTags($stripTags, $allowedTags, $content)
    {
        if (!$stripTags) {
            return $content;
        }

        // Process allowed tags
        $tags = explode(',', $allowedTags);
        foreach ($tags as &$tag) {
            $tag = '<' . trim($tag) . '>';
        }

        // Strip tags.
        $content = strip_tags($content, implode('', $tags));

        return $content;
    }
}
