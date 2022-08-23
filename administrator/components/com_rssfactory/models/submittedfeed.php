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

class RssFactoryBackendModelSubmittedFeed extends JModelAdmin
{
    public function getTable($type = 'SubmittedFeed', $prefix = 'RssFactoryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
    }

    public function batch($commands, $pks, $contexts)
    {
        // Sanitize user ids.
        $pks = array_unique($pks);
        \Joomla\Utilities\ArrayHelper::toInteger($pks);

        if (array_search(0, $pks, true)) {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks)) {
            $this->setState('error', JText::_('JGLOBAL_NO_ITEM_SELECTED'));
            return false;
        }

        $categoryId = $commands['category_id'];

        if (!$categoryId) {
            $this->setState('error', JText::_('COM_RSSFACTORY_SUBMITTEDFEEDS_BATCH_PUBLISH_ERROR_CATEGORY_NOT_SET'));
            return false;
        }

        foreach ($pks as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                continue;
            }

            $feed = $this->getTable('Feed');

            $data = array(
                'userid'    => $table->userid,
                'url'       => $table->url,
                'published' => 1,
                'date'      => $table->date,
                'cat'       => $categoryId,
                'title'     => $table->title,
            );

            if ($feed->save($data)) {
                $table->delete();
            }
        }

        return true;
    }
}
