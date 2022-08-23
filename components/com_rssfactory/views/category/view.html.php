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

class RssFactoryFrontendViewCategory extends FactoryViewRss
{
    protected
        $extension = 'com_rssfactory',
        $get = array(
        'categories',
        'category',
        'displayConfig',
    ),
        $registerHtml = array('RssFactoryFeeds');

    protected function prepareDocument()
    {
        parent::prepareDocument();

        $this->addPathway();
    }

    protected function addPathway()
    {
        $pathway = JFactory::getApplication()->getPathway();

        if ($this->category && 'root' != $this->category->id) {
            $path[] = array('title' => $this->category->title, 'link' => FactoryRouteRss::view('category&category_id=' . $this->category->id));
            $category = $this->category->getParent();

            while ($category->id > 1) {
                $path[] = array('title' => $category->title, 'link' => FactoryRouteRss::view('category&category_id=' . $category->id));
                $category = $category->getParent();
            }

            krsort($path);

            foreach ($path as $item) {
                $pathway->addItem($item['title'], $item['link']);
            }
        }
    }
}
