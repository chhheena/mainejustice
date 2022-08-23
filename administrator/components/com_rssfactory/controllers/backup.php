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

class RssFactoryBackendControllerBackup extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function restore()
    {
        /* @var $model RssFactoryBackendModelBackup */
        $model = $this->getModel('Backup');
        $app = JFactory::getApplication();
        $data = $app->input->files->get('restore_archive', array());

        // Restore data.
        if ($model->restoreBackup($data)) {
            $app->enqueueMessage(FactoryTextRss::_('backup_task_restore_success'), 'message');
        } else {
            $app->enqueueMessage(FactoryTextRss::_('backup_task_restore_error'), 'error');
        }

        // Set error messages.
        if ('' != $error = $model->getState('error')) {
            $app->enqueueMessage($error, 'error');
        }

        // Set notices.
        $restored = $model->getState('restored', array());

        if (isset($restored['feeds'])) {
            $app->enqueueMessage(FactoryTextRss::plural('backp_task_restore_notice_feeds', $restored['feeds']), 'notice');
        }

        if (isset($restored['categories'])) {
            $app->enqueueMessage(FactoryTextRss::plural('backp_task_restore_notice_categories', $restored['feeds']), 'notice');
        }

        if (isset($restored['ads'])) {
            $app->enqueueMessage(FactoryTextRss::plural('backp_task_restore_notice_ads', $restored['ads']), 'notice');
        }

        // Redirect.
        $this->setRedirect('index.php?option=' . $this->option . '&view=backup');

        return true;
    }

    public function generate()
    {
        /* @var $model RssFactoryBackendModelBackup */
        $model = $this->getModel('Backup');
        $app = JFactory::getApplication();

        // Restore data.
        if (!$model->generateBackup()) {
            $app->enqueueMessage(FactoryTextRss::_('backup_task_generate_error'), 'error');
            $app->enqueueMessage($model->getState('error'), 'error');
        }

        // Redirect.
        $this->setRedirect('index.php?option=' . $this->option . '&view=backup');

        return true;
    }

    public function import()
    {
        /* @var $model RssFactoryBackendModelBackup */
        $model = $this->getModel('Backup');
        $app = JFactory::getApplication();
        $separator = $app->input->post->getString('import_separator', 'TAB');
        $file = $app->input->files->get('import_file', array());

        // Restore data.
        if ($model->import($file, $separator)) {
            $app->enqueueMessage(FactoryTextRss::_('backup_task_import_success'), 'message');
        } else {
            $app->enqueueMessage(FactoryTextRss::_('backup_task_import_error'), 'error');
            $app->enqueueMessage($model->getState('error'), 'error');
        }

        // Redirect.
        $this->setRedirect('index.php?option=' . $this->option . '&view=backup');

        return true;
    }

    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));
    }
}
