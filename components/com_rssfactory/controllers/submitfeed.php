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

class RssFactoryFrontendControllerSubmitFeed extends JControllerForm
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'SubmitFeed', $prefix = 'RssFactoryFrontendModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function cancel($key = null)
    {
        parent::cancel($key);

        $this->setRedirect(JUri::base());
        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        if (parent::save($key, $urlVar)) {
            $this->setRedirect(FactoryRouteRss::view('submitfeed'));
            return true;
        }

        return false;
    }

    protected function allowAdd($data = array())
    {
        $allowed = JFactory::getUser()->authorise('frontend.submitfeed', $this->option);

        if (!$allowed) {
            JFactory::getApplication()->redirect(JUri::base());
            return false;
        }

        return true;
    }
}
