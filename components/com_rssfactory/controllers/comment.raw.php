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

class RssFactoryFrontendControllerComment extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Comment', $prefix = 'RssFactoryFrontendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function delete()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $commentId = $input->getInt('comment_id', 0);
        $model = $this->getModel();
        $response = array();

        $result = $model->delete($commentId);

        if ($result) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::_('comment_task_delete_success');
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('comment_task_delete_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }

    public function update()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $commentId = $input->getInt('comment_id', 0);
        $text = $input->getString('text', '');
        $model = $this->getModel();
        $response = array();

        $result = $model->update($commentId, $text);

        if ($result) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::_('comment_task_update_success');
            $response['text'] = nl2br($model->getState('text'));
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('comment_task_update_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }
}
