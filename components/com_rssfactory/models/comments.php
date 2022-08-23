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

class RssFactoryFrontendModelComments extends JModelList
{
    protected $option = 'com_rssfactory';
    protected $story = null;

    public function getStory($id = null)
    {
        if (is_null($this->story)) {
            $table = $this->getTable('Cache', 'RssFactoryTable');
            $this->story = false;

            if (is_null($id)) {
                $id = JFactory::getApplication()->input->getInt('story_id', 0);
            }

            if ($id && $table->load($id)) {
                $this->story = $table;
            }
        }

        if (!$this->story) {
            throw new Exception(FactoryTextRss::_('comments_error_story_not_found'), 404);
        }

        return $this->story;
    }

    public function getItems()
    {
        if (!$this->getStory()) {
            return array();
        }

        return parent::getItems();
    }

    public function getCategory()
    {
        $model = JModelLegacy::getInstance('Category', 'RssFactoryFrontendModel');
        $table = $this->getTable('Feed', 'RssFactoryTable');

        $table->load($this->story->rssid);

        return $model->getCategory($table->cat);
    }

    public function getCreateCommentsEnabled()
    {
        return RssFactoryHelper::isUserAuthorised('frontend.comment.create');
    }

    public function getDateFormat()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        return $configuration->get('date_format', 'l, d F Y');
    }

    public function getCommentsManage()
    {
        return RssFactoryHelper::isUserAuthorised('frontend.comment.manage');
    }

    public function getCommentView()
    {
        if (!$this->getCreateCommentsEnabled()) {
            return false;
        }

        $model = JModelAdmin::getInstance('Comment', 'RssFactoryFrontendModel');

        JLoader::register('RssFactoryFrontendViewComment', JPATH_COMPONENT_SITE . '/views/comment/view.html.php');
        $view = new RssFactoryFrontendViewComment();

        $view->setModel($model, true);

        return $view;
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();
        $id = JFactory::getApplication()->input->getInt('story_id', 0);
        $configuration = JComponentHelper::getParams('com_rssfactory');

        $query->select('c.*')
            ->from('#__rssfactory_comments c')
            ->where('c.type_id = ' . $query->quote(1) . ' AND c.item_id = ' . $query->quote($id))
            ->order('c.created_at DESC');

        // Select username.
        $query->select('u.username')
            ->leftJoin('#__users u ON u.id = c.user_id');

        // Filter published comments.
        if ($configuration->get('approveComments', 0)) {
            $query->where('c.published = ' . $query->quote(1));
        }

        return $query;
    }
}
