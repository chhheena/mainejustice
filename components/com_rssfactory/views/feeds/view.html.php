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

class RssFactoryFrontendViewFeeds extends FactoryViewRss
{
    protected $category;
    protected $items;
    protected $search;
    protected $searchEnabled;
    protected $ads;
    protected $pageTitle;
    protected $display;

    protected
        $extension = 'com_rssfactory',
        $html = array(
            'bootstrap.tooltip',
            'behavior.tooltip',
        ),
        $registerHtml = array('admin/feeds', 'rssfactoryfeeds'),
        $permissions = array('bookmarks' => 'frontend.favorites');

    public function display($tpl = null)
    {
        /** @var RssFactoryFrontendModelFeeds $model */
        $model = $this->getModel();

        $display = $model->getDisplay(\Joomla\CMS\Factory::getApplication()->getMenu());

        $this->category      = $model->getCategory();
        $this->items         = $model->getItems($display);
        $this->search        = $model->getSearch();
        $this->searchEnabled = $model->getSearchEnabled();
        $this->ads           = $model->getAds();
        $this->display       = $display;

        return parent::display($tpl);
    }

    protected function prepareDocument()
    {
        parent::prepareDocument();

        $this->addPathway();
    }

    protected function addPathway()
    {
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $pathway = JFactory::getApplication()->getPathway();
        $path = array();

        if (!$this->category) {
            if ($active) {
                $this->pageTitle = $active->title;
            } else {
                $this->pageTitle = FactoryTextRss::_('feeds_page_title_default');
                $path[] = array(
                    'title' => $this->pageTitle,
                    'link'  => '',
                );
            }
        } else {
            if ($active) {
                if ($this->isCurrentItemId($active)) {
                    if ($this->category->id == $active->query['category_id']) {
                        $this->pageTitle = $active->title;
                    } else {
                        $this->pageTitle = $this->category->title;
                        $path[] = array(
                            'title' => $this->pageTitle,
                            'link'  => '',
                        );

                        $category = $this->category->getParent();

                        if ($category) {
                            while ($category->id > 1) {
                                $path[] = array(
                                    'title' => $category->title,
                                    'link'  => FactoryRouteRss::view('feeds&category_id=' . $category->id),
                                );
                                $category = $category->getParent();
                            }
                        }
                    }
                }
            } else {
                $this->pageTitle = $this->category->title;
                $path[] = array(
                    'title' => $this->pageTitle,
                    'link'  => '',
                );

                $category = $this->category->getParent();

                if ($category) {
                    while ($category->id > 1) {
                        $path[] = array(
                            'title' => $category->title,
                            'link'  => FactoryRouteRss::view('feeds&category_id=' . $category->id),
                        );
                        $category = $category->getParent();
                    }
                }

                $path[] = array(
                    'title' => FactoryTextRss::_('feeds_page_title_default'),
                    'link'  => FactoryRouteRss::view('category'),
                );
            }
        }

        krsort($path);

        foreach ($path as $item) {
            $pathway->addItem($item['title'], $item['link']);
        }

        return true;
    }

    private function isCurrentItemId($active)
    {
        if ('com_rssfactory' !== $active->query['option']) {
            return false;
        }

        if ('feeds' !== $active->query['view']) {
            return false;
        }

        return true;
    }
}
