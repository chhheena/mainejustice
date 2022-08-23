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

    public function route()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $storyId = $input->getInt('story_id', 0);
        $model = $this->getModel();
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $behavior = $configuration->get('story_source_link_behavior', 'link');

        if ('toolbar' == $behavior) {
            $this->setRedirect(FactoryRouteRss::view('toolbar&tmpl=component&story_id=' . $storyId));
            return true;
        }

        $result = $model->getLinkForStory($storyId);

        if ($result) {
            $this->setRedirect($result);
            return true;
        }

        $app->enqueueMessage($model->getState('error'), 'error');

        $this->setRedirect(JRoute::_('index.php', false));
        return false;
    }
}
