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

class plgSystemRssFactory extends JPlugin
{
    public function onAfterDispatch()
    {
        // Initialise variables.
        $input = JFactory::getApplication()->input;

        // Check that we are in the site application.
        if (JFactory::getApplication()->isClient('administrator')) {
            return true;
        }

        // Check if it's a html request type and not a component template.
        if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component') {
            return true;
        }

        // Check last refresh.
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $usePseudoCron = $configuration->get('use_pseudocron', 1);

        // Check if Pseudo Cron is enabled.
        if (!$usePseudoCron) {
            return true;
        }

        $lastRefresh = $configuration->get('pseudocron_last_refresh', '');
        $interval = $configuration->get('refreshinterval', 1440);

        $limit = JFactory::getDate($lastRefresh . ' +' . $interval . ' minutes')->toSql();
        $now = JFactory::getDate()->toSql();

        if ($now < $limit && '' != $lastRefresh) {
            return true;
        }

        $class = 'RssFactoryHelper';
        JLoader::register($class, JPATH_ADMINISTRATOR . '/components/com_rssfactory/helpers/rssfactory.php');

        if (class_exists($class)) {
            /* @var $class RssFactoryHelper */
            $document = JFactory::getDocument();
            $buffer = $document->getBuffer('component');

            $document->setBuffer($buffer . $class::getPseudoCronHtml(), 'component');
        }
    }
}
