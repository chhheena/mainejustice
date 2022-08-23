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

    public function testFtp()
    {
        $model = $this->getModel('Feed');
        $data = $this->input->get('ftp', array(), 'array');

        if ($model->testFtp($data)) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::_('feed_task_testftp_success');
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('feed_task_testftp_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }

    public function refreshIcon()
    {
        $model = $this->getModel('Feed');
        $data = $this->input->get('data', array(), 'array');

        if ($model->refreshIcon($data)) {
            $response['status'] = 1;
            $response['message'] = JHtml::image($model->getState('feed.icon'), 'feed icon');
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('feed_task_refreshicon_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }
}
