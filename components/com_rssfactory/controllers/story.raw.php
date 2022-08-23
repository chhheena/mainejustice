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

class RssFactoryFrontendControllerStory extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Story', $prefix = 'RssFactoryFrontendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function vote()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $storyId = $input->getInt('story_id', 0);
        $value = $input->getInt('vote', 0);
        $userId = JFactory::getUser()->id;
        $ip = $input->server->getString('REMOTE_ADDR', '');
        $model = $this->getModel();
        $response = array();

        $result = $model->vote($storyId, $userId, $ip, $value);

        if ($result) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::_('story_task_' . $this->getTask() . '_success');
            $response['rating'] = $model->getState('rating');
            $response['storyId'] = $storyId;
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('story_task_' . $this->getTask() . '_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }
}
