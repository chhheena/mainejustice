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

class RssFactoryFrontendViewStory extends FactoryViewRss
{
    protected $story;
    protected $category;
    protected $items;
    protected $pagination;
    protected $state;
    protected $createCommentsEnabled;
    protected $commentsManage;
    protected $commentView;
    protected $dateFormat;
    protected $user;

    protected $html = array('jquery.framework');
    protected $js = array('growl');

    public function display($tpl = null)
    {
        /** @var RssFactoryFrontendModelComments $model */
        $model = JModelLegacy::getInstance('Comments', 'RssFactoryFrontendModel');

        $this->story = $model->getStory();
        $this->category = $model->getCategory();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->createCommentsEnabled = $model->getCreateCommentsEnabled();
        $this->commentsManage = $model->getCommentsManage();
        $this->commentView = $model->getCommentView();
        $this->dateFormat = $model->getDateFormat();
        $this->user = JFactory::getUser();

        return parent::display($tpl);
    }

    protected function prepareDocument()
    {
        parent::prepareDocument();

        $this->addPathway();
    }

    protected function addPathway()
    {
        $pathway = JFactory::getApplication()->getPathway();

        if ($this->category) {
            $path[] = array('title' => $this->story->item_title, 'link' => '');
            $path[] = array('title' => FactoryTextRss::_('category_path_stories'), 'link' => FactoryRouteRss::view('feeds&category_id=' . $this->category->id));
            $path[] = array('title' => $this->category->title, 'link' => FactoryRouteRss::view('category&category_id=' . $this->category->parent_id));
            $category = $this->category->getParent();

            while ($category->id > 1) {
                $path[] = array('title' => $category->title, 'link' => FactoryRouteRss::view('category&category_id=' . $category->parent_id));
                $category = $category->getParent();
            }

            krsort($path);

            foreach ($path as $item) {
                $pathway->addItem($item['title'], $item['link']);
            }
        }
    }
}
