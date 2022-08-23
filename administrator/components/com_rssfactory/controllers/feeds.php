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

class RssFactoryBackendControllerFeeds extends JControllerAdmin
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Feed', $prefix = 'RssFactoryBackendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function saveOrderAjax()
    {
        $pks = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        \Joomla\Utilities\ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }

    public function refresh()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to refresh from the request.
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');

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

        $extension = $this->input->get('extension');
        $extensionURL = ($extension) ? '&extension=' . $extension : '';
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
    }

    public function clearCache()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to clear cache from the request.
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');

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
                $ntext = $this->text_prefix . '_CACHE_CLEARED';
                $this->setMessage(JText::plural($ntext, count($cid)));
            }
        }

        $extension = $this->input->get('extension');
        $extensionURL = ($extension) ? '&extension=' . $extension : '';
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
    }
}
