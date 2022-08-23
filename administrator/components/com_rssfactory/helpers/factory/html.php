<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.2
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class FactoryHtml
{
    protected static $option = 'com_rssfactory';

    public static function script($file, $framework = false, $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
    {
        $file = self::parsePath($file);

        JHtml::script($file, $framework, $relative, $path_only, $detect_browser, $detect_debug);
    }

    public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
    {
        $file = self::parsePath($file, 'css');

        \Joomla\CMS\HTML\HTMLHelper::stylesheet($file, $attribs, $relative, $path_only, $detect_browser, $detect_debug);
    }

    public static function registerHtml($html)
    {
        $html = strtolower($html);
        $path = JPATH_COMPONENT_SITE;

        if (false !== strpos($html, 'admin/')) {
            $html = str_replace('admin/', '', $html);
            $path = JPATH_COMPONENT_ADMINISTRATOR;
        }

        $class = 'JHtml' . ucfirst($html);

        return JLoader::register($class, $path . '/helpers/html/' . $html . '.php');
    }

    protected static function parsePath($file, $type = 'js')
    {
        $path = array();
        $parts = explode('/', $file);

        $path[] = 'media';
        $path[] = self::$option;
        $path[] = 'assets';

        if ('admin' == $parts[0]) {
            $path[] = 'backend';
            unset($parts[0]);
            $parts = array_values($parts);
        } else {
            $path[] = 'frontend';
        }

        $path[] = $type;

        $count = count($parts);
        foreach ($parts as $i => $part) {
            if ($i + 1 == $count) {
                $path[] = $part . '.' . $type;
            } else {
                $path[] = $part;
            }
        }

        return implode('/', $path);
    }
}
