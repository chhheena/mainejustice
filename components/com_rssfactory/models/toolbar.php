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

class RssFactoryFrontendModelToolbar extends JModelLegacy
{
    protected $story = null;

    public function getStory($storyId = null)
    {
        if (is_null($this->story)) {
            $this->story = false;

            if (is_null($storyId)) {
                $storyId = JFactory::getApplication()->input->getInt('story_id', 0);
            }

            $dbo = $this->getDbo();
            $query = $dbo->getQuery(true)
                ->select('c.*')
                ->from('#__rssfactory_cache c');

            // Select total votes
            $query->select('COUNT(v.id) AS votes_total')
                ->leftJoin('#__rssfactory_voting v ON v.cacheId = c.id')
                ->group('c.id');

            // Select vote value.
            $query->select('vote.voteValue as vote_value')
                ->leftJoin('#__rssfactory_voting vote ON vote.cacheId = c.id AND vote.userid = ' . $dbo->quote(JFactory::getUser()->id));

            if ($storyId) {
                $query->where('c.id = ' . $dbo->quote($storyId));
            } else {
                $query->order('RAND()');
            }

            $result = $dbo->setQuery($query, 0, 1)
                ->loadObject();

            if ($result) {
                $this->story = $result;
            }
        }

        return $this->story;
    }

    public function getPrevStory()
    {
        return $this->getNearStory();
    }

    public function getNextStory()
    {
        return $this->getNearStory('next');
    }

    public function getVoteEnabled()
    {
        return JFactory::getUser()->authorise('frontend.voting', 'com_rssfactory');
    }

    protected function getNearStory($mode = 'prev')
    {
        if (!$this->story) {
            return false;
        }

        $operator = 'prev' == $mode ? '<' : '>';
        $order = 'prev' == $mode ? 'DESC' : 'ASC';

        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->select('c.id')
            ->from('#__rssfactory_cache c')
            ->where('c.item_date ' . $operator . ' ' . $dbo->quote($this->story->item_date))
            ->where('c.id <> ' . $dbo->quote($this->story->id))
            ->order('c.item_date ' . $order);

        $result = $dbo->setQuery($query, 0, 1)
            ->loadResult();

        return $result;
    }
}
