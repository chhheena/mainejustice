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

class JHtmlRssFactoryRules
{
    protected static $rules = null;

    public static function options()
    {
        $options = array();

        foreach (self::getRules() as $rule) {
            $options[$rule->getType()] = $rule->getLabel();
        }

        return $options;
    }

    public static function templates()
    {
        $templates = array();

        foreach (self::getRules() as $rule) {
            $templates[] = 'data-template-' . $rule->getType() . '="' . htmlentities($rule->getTemplate()) . '"';
        }

        return implode(' ', $templates);
    }

    protected static function getRules()
    {
        if (is_null(self::$rules)) {
            self::$rules = array();
            jimport('joomla.filesystem.folder');

            $path = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rules';
            $folders = JFolder::folders($path);

            foreach ($folders as $folder) {
                self::$rules[$folder] = RssFactoryRule::getInstance($folder);
            }
        }

        return self::$rules;
    }
}
