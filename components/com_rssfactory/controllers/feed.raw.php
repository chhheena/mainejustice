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

class RssFactoryFrontendControllerFeed extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Feed', $prefix = 'RssFactoryFrontendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function favorite()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $feedId = $input->getInt('feed_id', 0);
        //$return = base64_decode($input->getBase64('return', $_SERVER['HTTP_REFERER']));
        $value = $input->getInt('favorite', 0);
        $model = $this->getModel();
        $action = $value ? 'favorite' : 'unfavorite';
        $response = array();

        $result = $model->favorite($feedId, $value);

        if ($result) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::_('feed_task_' . $action . '_success');
            $response['feedId'] = $feedId;
            $response['url'] = FactoryRouteRss::task('feed.favorite&favorite=' . intval(!$value) . '&format=raw&feed_id=' . $feedId, false);
        } else {
            $response['status'] = 0;
            $response['message'] = FactoryTextRss::_('feed_task_' . $action . '_error');
            $response['error'] = $model->getState('error');
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }
}
