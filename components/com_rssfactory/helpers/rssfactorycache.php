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

class RssFactoryCache
{
    private static $instance = null;

    public static function getInstance()
    {
        $conf = JFactory::getConfig();
        $cachebase = $conf->get('cache_path', JPATH_SITE . '/cache');

        if (null === self::$instance) {
            self::$instance = new JCache(array(
                'caching'      => true,
                'defaultgroup' => 'com_rssfactory',
                'lifetime'     => 60 * 60 * 24 * 30,
                'cachebase'    => $cachebase,
            ));
        }

        return self::$instance;
    }
}
