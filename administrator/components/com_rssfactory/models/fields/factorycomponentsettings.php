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

class JFormFieldFactoryComponentSettings extends JFormField
{
    protected $type = 'FactoryComponentSettings';

    protected function getInput()
    {
        $output = $this->getOutput($this->element['option']);

        return $output;
    }

    protected function getOutput($option)
    {
        $output = array();
        /* @var $model RssFactoryBackendModelAbout */
        $model = JModelAdmin::getInstance('About', 'RssFactoryBackendModel');

        switch ($option) {
            case 'current_version':
                $output[] = $model->getCurrentVersion();
                break;

            case 'latest_version':
                $output[] = $model->getLatestVersion();
                break;
        }

        return implode("\n", $output);
    }
}
