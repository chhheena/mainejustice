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

class RssFactoryBackendControllerConfiguration extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->registerTask('apply', 'save');
    }

    public function save()
    {
        $user = JFactory::getUser();

        if (!$user->authorise('backend.settings', $this->option)) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
            return false;
        }

        $model = $this->getModel('Configuration');
        $form = $model->getForm();
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');
        $app = JFactory::getApplication();

        $return = $model->validate($form, $data);

        if ($return === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Redirect back to the edit screen.
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=configuration', false));
            return false;
        }

        if ($model->save($return)) {
            $msg = FactoryTextRss::_('configuration_task_save_success');
        } else {
            $app->enqueueMessage($model->getState('error'), 'error');
            $msg = FactoryTextRss::_('configuration_task_save_error');
        }

        $link = 'index.php?option=' . $this->option;

        if ('apply' == $this->getTask()) {
            $link .= '&view=configuration';
        }

        $this->setRedirect($link, $msg);

        return true;
    }

    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));
    }
}
