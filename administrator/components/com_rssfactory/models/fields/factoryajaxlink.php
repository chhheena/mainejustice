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

class JFormFieldFactoryAjaxLink extends JFormField
{
    protected $type = 'FactoryAjaxLink';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        FactoryHtmlRss::script('admin/fields/factoryajaxlink');

        $output = array();

        $option = JFactory::getApplication()->input->getCmd('option', '');
        $url = 'index.php?option=' . $option . '&task=' . $this->element['task'] . '&format=json';
        $update = $this->element['update'];

        $output[] = '<input type="button" id="' . $this->id . '" data-update="' . $update . '" data-url="' . $url . '" value="' . $this->element['label'] . '" class="btn btn-small btn-primary factory-ajax-link">';

        return implode("\n", $output);
    }
}
