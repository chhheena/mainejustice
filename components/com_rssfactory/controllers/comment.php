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

class RssFactoryFrontendControllerComment extends JControllerForm
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Comment', $prefix = 'RssFactoryFrontendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function save($key = null, $urlVar = null)
    {
        $result = parent::save($key, $urlVar);

        $id = JFactory::getApplication()->input->getInt('story_id', 0);
        $this->setRedirect(FactoryRouteRss::view('comments&story_id=' . $id));

        $configuration = JComponentHelper::getParams('com_rssfactory');
        if ($result && $configuration->get('approveComments', 0)) {
            $this->setMessage(FactoryTextRss::_('comment_task_save_success_approval'));
        }

        return $result;
    }

    protected function allowAdd($data = array())
    {
        return RssFactoryHelper::isUserAuthorised('frontend.comment.create');
    }
}
