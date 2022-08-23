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

class RssFactoryBackendModelCache extends JModelLegacy
{
    public function clear()
    {
        $dbo = $this->getDbo();

        $query = $dbo->getQuery(true)
            ->select('c.id')
            ->from('#__rssfactory_cache AS c');
        $results = $dbo->setQuery($query)
            ->loadAssocList('id');
        $results = array_keys($results);

        $result = $dbo->setQuery('TRUNCATE TABLE #__rssfactory_cache')
            ->execute();

        if (!$result) {
            return false;
        }

        JPluginHelper::importPlugin('finder');

        foreach ($results as $id) {
            \Joomla\CMS\Factory::getApplication()->triggerEvent('onFinderAfterDelete', array(
                'com_rssfactory.story',
                $id
            ));
        }

        // Remove all votes.
        $dbo = $this->getDbo();
        $dbo->setQuery('TRUNCATE TABLE #__rssfactory_voting')
            ->execute();

        // Remove all comments.
        $dbo = $this->getDbo();
        $dbo->setQuery('TRUNCATE TABLE #__rssfactory_comments')
            ->execute();

        return true;
    }

    public function optimize()
    {
        $dbo = $this->getDbo();
        $result = $dbo->setQuery('OPTIMIZE TABLE #__rssfactory_cache')
            ->execute();

        return $result;
    }
}
