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

jimport('joomla.application.component.modeladmin');

class RssFactoryBackendModelConfiguration extends JModelAdmin
{
    protected $option = 'com_rssfactory';
    protected $fieldsets = null;

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        JForm::addFormPath(JPATH_ADMINISTRATOR . DS . 'components' . DS . $this->option);

        /* @var $form JForm */
        $form = $this->loadForm(
            $this->option . '.' . $this->getName(),
            $this->getName(),
            array(
                'control'   => 'jform',
                'load_data' => $loadData),
            false,
            '/config');

        if (empty($form)) {
            return false;
        }

        $form->loadFile($this->getName() . '.import2content', false, '/config');

        $data = $loadData ? $this->loadFormData() : array();
        $this->preprocessForm($form, $data);

        if ($loadData) {
            $form->bind($data);
        }

        $this->fieldsets = $form->getFieldsets();

        return $form;
    }

    public function save($data)
    {
        // Save the rules.
        if (isset($data['rules'])) {
            jimport('joomla.access.rules');
            $rules = new JAccessRules($data['rules']);
            $asset = JTable::getInstance('asset');

            if (!$asset->loadByName($this->option)) {
                $root = JTable::getInstance('asset');
                $root->loadByName('root.1');
                $asset->name = $this->option;
                $asset->title = $this->option;
                $asset->setLocation($root->id, 'last-child');
            }

            $asset->rules = (string)$rules;

            if (!$asset->check() || !$asset->store()) {
                return false;
            }

            unset($data['rules']);
        }

        // Save component settings.
        $extension = JTable::getInstance('Extension');
        $id = $extension->find(array('element' => $this->option, 'type' => 'component'));
        $settings = JComponentHelper::getParams($this->option);

        $extension->load($id);
        $extension->bind(array('params' => array_merge($settings->toArray(), $data)));

        if (!$extension->store()) {
            return false;
        }

        // Clean the component cache.
        $this->cleanCache('_system', 1);

        return true;
    }

    public function getFieldsets()
    {
        return $this->fieldsets;
    }

    protected function loadFormData()
    {
        $result = JComponentHelper::getComponent($this->option);

        return $result->params;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
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
