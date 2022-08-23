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

class RssFactoryFrontendViewFeeds extends FactoryViewRss
{
    protected $stories;
    protected $pagination;
    protected $ads;
    protected $display;

    protected
        $extension = 'com_rssfactory',
        $registerHtml = array('admin/feeds', 'rssfactoryfeeds'),
        $layout = 'feed';

    public function display($tpl = null)
    {
        /** @var RssFactoryFrontendModelFeeds $model */
        $model = $this->getModel();

        $display = $model->getDisplay(\Joomla\CMS\Factory::getApplication()->getMenu());

        $this->stories    = $model->getStories($display);
        $this->pagination = $model->getPagination();
        $this->ads        = $model->getAds();
        $this->display    = $display;

        return parent::display($tpl);
    }
}
