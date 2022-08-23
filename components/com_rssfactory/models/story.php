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

class RssFactoryFrontendModelStory extends JModelLegacy
{
    protected $option = 'com_rssfactory';

    public function vote($storyId, $userId, $ip, $value)
    {
        // Initialise variables.
        $hash = sha1($userId . $ip);
        $errorText = 'story_vote_error';

        // Check if vote is valid.
        if (!in_array($value, array(-1, 1))) {
            $this->setState('error', FactoryTextRss::_($errorText . '_vote_not_valid'));
            return false;
        }

        // Check if user is allowed to vote.
        if (!RssFactoryHelper::isUserAuthorised('frontend.voting')) {
            $this->setState('error', FactoryTextRss::_($errorText . '_not_allowed_to_vote'));
            return false;
        }

        // Get the story.
        $table = $this->getTable('Cache', 'RssFactoryTable');
        if (!$storyId || !$table->load($storyId)) {
            $this->setState('error', FactoryTextRss::_($errorText . '_story_not_found'));
            return false;
        }

        // Check if the user has already voted for this story.
        $table = $this->getTable('Vote', 'RssFactoryTable');
        if ($table->load(array('voteHash' => $hash, 'cacheId' => $storyId))) {
            $this->setState('error', FactoryTextRss::_($errorText . '_already_voted'));
            return false;
        }

        // Register the new vote.
        $data = array(
            'cacheId'   => $storyId,
            'voteHash'  => $hash,
            'userid'    => $userId,
            'voteValue' => $value,
        );

        if (!$table->save($data)) {
            return false;
        }

        // Get the new rating of the story.
        $this->setState('rating', $this->getStoryRating($storyId));

        return true;
    }

    public function getLinkForStory($storyId)
    {
        $table = $this->getTable('Cache', 'RssFactoryTable');

        if (!$storyId || !$table->load($storyId)) {
            $this->setState('error', FactoryTextRss::_('story_error_not_found'));
            return false;
        }

        $table->hit();

        return $table->item_link;
    }

    protected function getStoryRating($storyId)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->select('SUM(v.voteValue)')
            ->from('#__rssfactory_voting v')
            ->where('v.cacheId = ' . $dbo->quote($storyId));

        $result = $dbo->setQuery($query)
            ->loadResult();

        return $result;
    }
}
