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

class JHtmlRssFactory
{
    public static function itemDropDown($options = array())
    {
        if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
            return self::itemDropDown3($options);
        }

        return self::itemDropDown4($options);
    }

    private static function itemDropDown4($options = array())
    {
        return '';
    }

    private static function itemDropDown3($options = array())
    {
        foreach ($options as $option => $params) {
            switch ($option) {
                case 'edit':
                    JHtml::_('dropdown.edit', $params['id'], $params['prefix'] . '.');
                    break;

                case 'publish':
                    $method = $params['published'] ? 'unpublish' : 'publish';
                    JHtml::_('dropdown.' . $method, 'cb' . $params['i'], $params['prefix'] . '.');
                    break;

                case 'divider':
                    JHtml::_('dropdown.divider');
                    break;

                case 'refresh':
                    $task = $params['prefix'] . '.refresh';
                    JHtml::_('dropdown.addCustomItem', FactoryTextRss::_('feeds_list_feed_refresh'), 'javascript:void(0)', 'onclick="contextAction(\'cb' . $params['i'] . '\', \'' . $task . '\')"');
                    break;

                case 'clearcache':
                    $task = $params['prefix'] . '.clearcache';
                    JHtml::_('dropdown.addCustomItem', FactoryTextRss::_('feeds_list_feed_clear_cache'), 'javascript:void(0)', 'onclick="contextAction(\'cb' . $params['i'] . '\', \'' . $task . '\')"');
                    break;
            }
        }

        return JHtmlDropdown::render();
    }
}
