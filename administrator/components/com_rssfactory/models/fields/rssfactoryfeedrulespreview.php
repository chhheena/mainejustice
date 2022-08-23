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

class JFormFieldRssFactoryFeedRulesPreview extends JFormField
{
    protected $type = 'RssFactoryFeedRulesPreview';
    protected $limit = 10;

    protected function getLabel()
    {
        if ('false' == $this->element['hasLabel']) {
            return '';
        }

        return parent::getLabel();
    }

    protected function getInput()
    {
        $id = $this->form->getValue('id');
        $stories = $this->getStories($id);

        $output = array();

        $output[] = '<div id="' . $this->element['name'] . '">';

        if ($stories) {
            $output[] = FactoryTextRss::sprintf('field_rules_preview_info', min($this->limit, count($stories)));
            $output[] = '<ul class="latest-stories">';
            foreach ($stories as $story) {
                $output[] = '<li><input type="radio" name="' . $this->name . '" value="' . urlencode($story->item_link) . '" /><a href="' . $story->item_link . '" target="_blank">' . $story->item_title . '</a></li>';
            }
            $output[] = '</ul>';
            $output[] = '<button type="button" class="btn btn-primary preview-rules"><i class="icon-search icon-white"></i>&nbsp;' . FactoryTextRss::_('field_rules_preview_button') . '</button>';
        } else {
            $output[] = FactoryTextRss::_('field_rules_preview_info_no_stories_cached');
        }

        $output[] = '</div>';

        return implode("\n", $output);
    }

    protected function getStories($id)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true)
            ->select('c.id, c.item_link, c.item_title')
            ->from('#__rssfactory_cache c')
            ->where('c.rssid = ' . $dbo->quote($id))
            ->order('c.date DESC');

        $results = $dbo->setQuery($query, 0, $this->limit)
            ->loadObjectList();

        return $results;
    }
}
