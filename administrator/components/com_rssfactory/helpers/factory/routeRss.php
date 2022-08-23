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

class FactoryRouteRss
{
    protected static $option = 'com_rssfactory';

    public static function _($url = '', $xhtml = false, $ssl = null)
    {
        $url = 'index.php?option=' . self::$option . ($url != '' ? '&' . $url : '');

        return JRoute::_($url, $xhtml, $ssl);
    }

    public static function view($view, $xhtml = false, $ssl = null)
    {
        $url = 'view=' . $view;

        return self::_($url, $xhtml, $ssl);
    }

    public static function task($task, $xhtml = false, $ssl = null)
    {
        $url = 'task=' . $task;

        return self::_($url, $xhtml, $ssl);
    }
}
