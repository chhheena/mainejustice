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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/loader.php';

// Check if user has access to the backend section
if (!JFactory::getUser()->authorise('backend.access', 'com_rssfactory')) {
    JToolbarHelper::title(JText::_('JERROR_ALERTNOAUTHOR'));
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

require_once JPATH_SITE . '/components/com_rssfactory/vendor/autoload.php';

RssFactoryHelper::setSqlMode();

$controller = JControllerLegacy::getInstance('RssFactoryBackend');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();

RssFactoryHelper::resetSqlMode();
