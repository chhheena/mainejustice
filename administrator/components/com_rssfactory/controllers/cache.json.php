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

class RssFactoryBackendControllerCache extends JControllerLegacy
{
    protected $option = 'com_rssfactory';

    public function clear()
    {
        $model = $this->getModel('Cache');
        $response = array();

        if ($model->clear()) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::plural('form_field_rssfactoryproinfo_cache_content', 0);
        } else {
            $response['status'] = 0;
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }

    public function optimize()
    {
        $model = $this->getModel('Cache');
        $response = array();

        if ($model->clear()) {
            $response['status'] = 1;
            $response['message'] = FactoryTextRss::sprintf('form_field_rssfactoryproinfo_cache_table_status', 0);
        } else {
            $response['status'] = 0;
        }

        echo json_encode($response);

        JFactory::getApplication()->close();
    }
}
