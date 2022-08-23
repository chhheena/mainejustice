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

class FactoryText
{
    protected static $option = 'com_rssfactory';

    public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
    {
        $string = strtoupper(self::$option . '_' . str_replace(' ', '_', $string));

        return JText::_($string, $jsSafe, $interpretBackSlashes, $script);
    }

    public static function sprintf()
    {
        $args = func_get_args();
        $args[0] = strtoupper(self::$option . '_' . $args[0]);

        return call_user_func_array(array('JText', 'sprintf'), $args);
    }

    public static function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
    {
        $string = strtoupper(self::$option . '_' . $string);

        return JText::script($string, $jsSafe, $interpretBackSlashes);
    }

    public static function plural($string, $n)
    {
        $args = func_get_args();
        $args[0] = strtoupper(self::$option . '_' . $args[0]);

        return call_user_func_array(array('JText', 'plural'), $args);
    }
}
