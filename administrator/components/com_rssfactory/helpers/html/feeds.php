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

class JHtmlFeeds
{
    public static function icon($feedId, $url = false, array $attributes = [])
    {
        jimport('joomla.filesyste.file');

        $filename = 'default.png';
        $path = JPATH_SITE . '/media/com_rssfactory/icos/ico_' . md5($feedId) . '.png';

        if (JFile::exists($path)) {
            $filename = 'ico_' . md5($feedId) . '.png';
        }

        $src = JUri::root() . 'media/com_rssfactory/icos/' . $filename;

        if ($url) {
            return $src;
        }

        $img = JHTML::image($src, 'ico' . $feedId, $attributes);

        return $img;
    }
}
