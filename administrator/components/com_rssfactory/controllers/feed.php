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

class RssFactoryBackendControllerFeed extends JControllerForm
{
    protected $option = 'com_rssfactory';

    public function refresh()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to refresh from the request.
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $cid = array($id);

        if (empty($cid)) {
            JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            \Joomla\Utilities\ArrayHelper::toInteger($cid);

            // Refresh the items.
            if (!$model->refresh($cid)) {
                JLog::add($model->getState('error'), JLog::WARNING, 'jerror');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_REFRESHED';
                $this->setMessage(JText::plural($ntext, count($cid)));
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=feed&layout=edit&id=' . $id, false));
    }

    public function clearCache()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to clear cache from the request.
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $cid = array($id);

        if (empty($cid)) {
            JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            \Joomla\Utilities\ArrayHelper::toInteger($cid);

            // Refresh the items.
            if (!$model->clearCache($cid)) {
                JLog::add($model->getState('error'), JLog::WARNING, 'jerror');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_CLEARED_CACHE';
                $this->setMessage(JText::plural($ntext, count($cid)));
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=feed&layout=edit&id=' . $id, false));
    }

    public function move()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $batch = $input->get('batch', array(), 'array');
        $cid = $input->get('cid', array(), 'array');
        $model = $this->getModel();

        if ($model->move($cid, $batch)) {
            $msg = FactoryTextRss::_('feed_task_move_success');
        } else {
            $msg = FactoryTextRss::_('feed_task_move_error');
            $app->enqueueMessage($model->getState('error'), 'warning');
        }

        $this->setRedirect(FactoryRouteRss::view('feeds'), $msg);
    }
}
