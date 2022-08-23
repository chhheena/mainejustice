<?php
/**
 * @package         Advanced Module Manager
 * @version         9.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
    protected static $params = null;

    public static function getRegex($get_surrounding = false)
    {
    }

    public static function get()
    {
        if ( ! is_null(self::$params))
        {
            return self::$params;
        }

        $params = RL_Parameters::getComponent('advancedmodules');

        self::$params = $params;

        return self::$params;
    }

    public static function getTagCharacters()
    {
    }

    public static function setTagCharacters()
    {
    }
}
