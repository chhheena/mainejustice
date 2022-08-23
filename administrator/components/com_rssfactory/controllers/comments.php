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

class RssFactoryBackendControllerComments extends JControllerAdmin
{
    protected $option = 'com_rssfactory';

    public function getModel($name = 'Comment', $prefix = 'RssFactoryBackendModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);;
    }
}
