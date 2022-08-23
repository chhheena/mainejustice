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

class RssFactoryBackendModelAd extends JModelAdmin
{
    public $option = 'com_rssfactory';

    public function __construct($config = array())
    {
        $config['event_after_save'] = 'onRssFactoryProAdAfterSave';
        $config['event_after_delete'] = 'onRssFactoryProAdAfterDelete';

        parent::__construct($config);

        \Joomla\CMS\Factory::getApplication()->registerEvent($this->event_after_save, $this->event_after_save);
        \Joomla\CMS\Factory::getApplication()->registerEvent($this->event_after_delete, $this->event_after_delete);
    }

    public function getTable($type = 'Ad', $prefix = 'RssFactoryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        /* @var $form JForm */
        $form = $this->loadForm(
            $this->option . '.' . $this->getName(),
            $this->getName(),
            array(
                'control'   => 'jform',
                'load_data' => $loadData,
            ),
            false,
            '/form');

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk = null);

        // Convert to the JObject before adding other data.
        $properties = $item->getProperties(1);
        $item = \Joomla\Utilities\ArrayHelper::toObject($properties, 'JObject');

        if (property_exists($item, 'categories_assigned')) {
            $registry = new JRegistry;
            $registry->loadString($item->categories_assigned);
            $item->categories_assigned = $registry->toArray();
        }

        return $item;
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

function onRssFactoryProAdAfterDelete($event, $table = null)
{
    if ($event instanceof \Joomla\Event\Event) {
        $arguments = $event->getArguments();
        $table = $arguments[1];
    }

    $dbo = \Joomla\CMS\Factory::getDbo();

    // Remove old categories.
    $query = $dbo->getQuery(true)
        ->delete()
        ->from('#__rssfactory_ad_category_map')
        ->where('adId = ' . $dbo->quote($table->id));
    $dbo->setQuery($query)
        ->execute();

    return true;
}

function onRssFactoryProAdAfterSave($event, $table = null)
{
    if ($event instanceof \Joomla\Event\Event) {
        $arguments = $event->getArguments();
        $table = $arguments[1];
    }

    $categories = new JRegistry($table->categories_assigned);
    $categories = $categories->toArray();
    $dbo = \Joomla\CMS\Factory::getDbo();

    \Joomla\Utilities\ArrayHelper::toInteger($categories);

    // Remove old categories.
    $query = $dbo->getQuery(true)
        ->delete()
        ->from('#__rssfactory_ad_category_map')
        ->where('adId = ' . $dbo->quote($table->id));
    $dbo->setQuery($query)
        ->execute();

    // Store new categories.
    if (!$categories) {
        return true;
    }

    foreach ($categories as $category) {
        if (!$category) {
            continue;
        }

        $map = JTable::getInstance('AdCategoryMap', 'RssFactoryTable');

        $data = array(
            'adId'       => $table->id,
            'categoryId' => $category,
        );

        $map->save($data);
    }

    return true;
}
