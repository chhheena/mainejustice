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

class RssFactoryBackendViewAbout extends FactoryViewRss
{
    public $aboutHelper;

    public function display($tpl = null)
    {
        JLoader::register('AboutHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/about.php');
        $this->aboutHelper = new AboutHelper('rss');

        parent::display($tpl);

        JToolBarHelper::title(JText::_('COM_RSSFACTORY_ABOUT_PAGE_TITLE'));
    }
}
