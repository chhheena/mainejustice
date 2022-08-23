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

class JFormFieldRssFactoryExtendList extends JFormFieldList
{
    private $xml = null;

    protected function getOptions()
    {
        \Joomla\CMS\Factory::getLanguage()->load('com_rssfactory');

        $options = array();

        if ($this->useGlobal()) {
            $options[] = $this->getGlobalOption();
        }

        $options = array_merge($options, $this->getParentOptions(), parent::getOptions());

        return $options;
    }

    private function useGlobal()
    {
        if ('false' === $this->element['useGlobal']) {
            return false;
        }

        return (boolean)$this->element['useGlobal'];
    }

    private function getGlobalOption()
    {
        $settings = \Joomla\CMS\Component\ComponentHelper::getParams('com_rssfactory');
        $extendedFieldName = (string)$this->element['extend'];
        $globalValue = $settings->get($extendedFieldName);

        $globalName = (string)$this->getXml()->xpath('//field[@name="' . $extendedFieldName . '"]/option[@value="' . $globalValue . '"]')[0];
        $globalName = \Joomla\CMS\Language\Text::_($globalName);

        return array(
            'value' => 'global',
            'text' => sprintf('Use Global (%s)', $globalName),
        );
    }

    private function getParentOptions()
    {
        $extendedFieldName = (string)$this->element['extend'];

        $options = array();

        foreach ($this->getXml()->xpath('//field[@name="' . $extendedFieldName . '"]/option') as $option) {
            $options[] = array(
                'value' => (string)$option->attributes()->value,
                'text' => \Joomla\CMS\Language\Text::_((string)$option),
            );
        }

        return $options;
    }

    private function getXml()
    {
        if (null === $this->xml) {
            $this->xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_rssfactory/configuration.xml');
        }

        return $this->xml;
    }
}
