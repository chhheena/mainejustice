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

JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

class PlgFinderRssFactory extends FinderIndexerAdapter
{
	protected $context = 'Rss Factory';

	protected $extension = 'com_rssfactory';

    protected $layout = 'story';

	protected $type_title = 'Rss Factory';

	protected $table = '#__rssfactory_cache';

	protected $state_field = null;

	protected $autoloadLanguage = false;

    public function onFinderAfterDelete($context, $id)
    {
        if ($context !== 'com_rssfactory.story') {
            return null;
        }

        return $this->remove($id);
    }

    public function onFinderAfterSave($context, $cache)
    {
        if ($context !== 'com_rssfactory.story') {
            return null;
        }

        $this->reindex($cache->id);
    }

	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled.
		if (JComponentHelper::isEnabled($this->extension) === false) {
			return;
		}

        $item->title = $item->getElement('item_title');
		$item->summary = $item->getElement('item_description');
        $item->body = $item->getElement('item_description');
        
        $item->url = 'index.php?option=com_rssfactory&view=story&story_id=' . $item->id;
        $item->route ='index.php?option=com_rssfactory&view=story&story_id=' . $item->id;

        $item->access = 1;
		$item->state = 1;

        $item->addTaxonomy('Type', 'Rss Factory Story');

        $this->indexer->index($item);
    }

	protected function setup()
	{
		return true;
	}

	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();

		// Check if we can use the supplied SQL query.
		$query =
            $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.*')
			->from($this->table . ' AS a');

		return $query;
	}

    protected function getUrl($id, $extension, $view)
    {
        return 'index.php?option=' . $extension . '&view=' . $view . '&story_id=' . $id;
    }
}
