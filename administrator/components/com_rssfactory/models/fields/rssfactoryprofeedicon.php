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

class JFormFieldRssFactoryProFeedIcon extends JFormField
{
    protected $type = 'RssFactoryProIcon';

    protected function getInput()
    {
        JLoader::register('JHtmlFeeds', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html/feeds.php');

        $output = array();

        $output[] = '<div id="' . $this->element['name'] . '">';
        $output[] = JHtml::_('feeds.icon', $this->form->getValue('id'));
        $output[] = '</div>';

        return implode("\n", $output);
    }
}
