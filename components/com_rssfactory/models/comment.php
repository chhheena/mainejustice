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

class RssFactoryFrontendModelComment extends JModelAdmin
{
    protected $option = 'com_rssfactory';
    protected $event_after_save = 'onRssFactoryCommentAfterSave';

    public function __construct($config = array())
    {
        parent::__construct($config);

        \Joomla\CMS\Factory::getApplication()->registerEvent($this->event_after_save, $this->event_after_save);
    }

    public function delete(&$commentId)
    {
        // Check if user is authorized to delete comment.
        if (!RssFactoryHelper::isUserAuthorised('frontend.comment.manage')) {
            $this->setState('error', FactoryTextRss::_('comment_delete_error_not_allowed'));
            return false;
        }

        // Load comment.
        $table = JTable::getInstance('Comment', 'RssFactoryTable');
        if (!$commentId || !$table->load($commentId)) {
            $this->setState('error', FactoryTextRss::_('comment_delete_error_not_found'));
            return false;
        }

        // Remove comment.
        if (!$table->delete()) {
            return false;
        }

        return true;
    }

    public function update($commentId, $text)
    {
        // Check if user is authorized to update comment.
        if (!RssFactoryHelper::isUserAuthorised('frontend.comment.manage')) {
            $this->setState('error', FactoryTextRss::_('comment_update_error_not_allowed'));
            return false;
        }

        // Load comment.
        $table = JTable::getInstance('Comment', 'RssFactoryTable');
        if (!$commentId || !$table->load($commentId)) {
            $this->setState('error', FactoryTextRss::_('comment_update_error_not_found'));
            return false;
        }

        // Update comment.
        $table->text = $text;

        if (!$table->store()) {
            return false;
        }

        $this->setState('text', $table->text);

        return true;
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

    public function getTable($type = 'Comment', $prefix = 'RssFactoryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getStoryId()
    {
        return JFactory::getApplication()->input->getInt('story_id', 0);
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $context = $this->option . '.edit.' . $this->getName();
        $data = $app->getUserState($context . '.data', array());

        $data['item_id'] = JFactory::getApplication()->input->getInt('story_id', 0);

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

function onRssFactoryCommentAfterSave($event, $table, $isNew)
{
    if (!$isNew) {
        return true;
    }

    $users = array();
    $settings = JComponentHelper::getParams('com_rssfactory');

    foreach ($settings->get('comments.notification', array()) as $group) {
        $users = array_merge($users, JAccess::getUsersByGroup($group));
    }

    if (!$users) {
        return true;
    }

    $mailer = JFactory::getMailer();
    $mailer->setBody(FactoryTextRss::sprintf('comment_notification_body', $table->text));
    $mailer->setSubject(FactoryTextRss::_('comment_notification_subject'));
    $mailer->isHtml(true);

    foreach ($users as $user) {
        $user = JFactory::getUser($user);
        $mailer->addRecipient($user->email);
    }

    $mailer->send();

    return true;
}
