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

class RssFactoryFrontendController extends JControllerLegacy
{
    protected $default_view = 'category';

    public function __construct(array $config)
    {
        parent::__construct($config);

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onFrontendControllerConstruct', array(
            'com_rssfactory',
            $this,
        ));
    }
}
