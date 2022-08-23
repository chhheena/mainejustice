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

class JFormFieldRssFactoryProInfo extends JFormField
{
    protected $type = 'RssFactoryProInfo';

    protected function getLabel()
    {
        if ('false' == $this->element['hasLabel']) {
            return '';
        }

        return parent::getLabel();
    }

    protected function getInput()
    {
        $output = array();

        $output[] = '<div id="' . $this->element['name'] . '">';
        $output[] = $this->getOutput($this->element['option']);
        $output[] = '</div>';

        return implode("\n", $output);
    }

    protected function getOutput($option)
    {
        $output = array();
        $dbo = JFactory::getDbo();

        switch ($option) {
            case 'cache_content':
                $query = $dbo->getQuery(true)
                    ->select('COUNT(c.id)')
                    ->from('#__rssfactory_cache c')
                    ->where('c.archived = ' . $dbo->quote(0));
                $result = $dbo->setQuery($query)
                    ->loadResult();

                $output[] = FactoryTextRss::plural('form_field_rssfactoryproinfo_cache_content', $result);
                break;

            case 'cache_table_status':
                $query = ' SHOW TABLE STATUS LIKE ' . $dbo->quote('%rssfactory_cache');
                $result = $dbo->setQuery($query)
                    ->loadAssoc();
                $result = number_format(($result['Data_free'] / 1024), 2);

                $output[] = FactoryTextRss::sprintf('form_field_rssfactoryproinfo_cache_table_status', $result);
                break;

            case 'refresh_link':
                $password = $this->form->getValue('refresh_password');
                $link = JURI::root() . 'components/com_rssfactory/helpers/refresh.php?password=' . $password;
                $output[] = '<a href="' . $link . '" target="_blank">' . $link . '</a>';
                break;

            case 'text':
                $output[] = FactoryTextRss::_($this->element['default']);
                break;

            case 'link':
                $link = $this->element['link'];
                $output[] = '<a href="' . $link . '">' . FactoryTextRss::_($this->element['default']) . '</a>';
                break;
        }

        return implode("\n", $output);
    }
}
