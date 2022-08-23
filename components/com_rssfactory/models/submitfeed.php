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

class RssFactoryFrontendModelSubmitFeed extends JModelAdmin
{
    protected $option = 'com_rssfactory';
    protected $params = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $menu = JFactory::getApplication()->getMenu()->getActive();
        $this->params = $menu ? $menu->params : new JRegistry();
    }

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            $this->option . '.' . $this->getName(),
            $this->getName(),
            array(
                'control'   => 'jform',
                'load_data' => $loadData,
            ));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    public function getTable($type = 'SubmittedFeed', $prefix = 'RssFactoryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $context = $this->option . '.edit.' . $this->getName();
        $data = $app->getUserState($context . '.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        if (!$this->params->get('use_captcha', 1)) {
            $form->removeField('captcha');
        }

        parent::preprocessForm($form, $data, $group);

        $formName = str_replace('.', '_', $form->getName());

        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($form->getFieldset($fieldset->name) as $field) {
                $fieldName = ($field->group ? $field->group . '_' : '') . $field->fieldname;

                $label = $form->getFieldAttribute($field->fieldname, 'label', '', $field->group);

                if ('' == $label) {
                    $label = JText::_(strtoupper($formName . '_form_field_' . $fieldName . '_label'));
                    $form->setFieldAttribute($field->fieldname, 'label', $label, $field->group);
                }

                $desc = $form->getFieldAttribute($field->fieldname, 'description', '', $field->group);

                if ('' == $desc) {
                    $desc = JText::_(strtoupper($formName . '_form_field_' . $fieldName . '_desc'));
                    $form->setFieldAttribute($field->fieldname, 'description', $desc, $field->group);
                }
            }
        }
    }
}
