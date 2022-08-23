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

class FactoryMenu
{
    public static function createMenu($menu, $items, $component, $module)
    {
        // Check if menu already exists.
        if (self::menuTypeExists($menu['menutype'])) {
            return true;
        }

        // Create menu.
        self::createMenuType($menu);

        // Create menu items.
        self::createMenuItems($menu, $items, $component);

        // Create menu module.
        self::createMenuModule($menu, $module);

        return true;
    }

    protected static function menuTypeExists($menuType)
    {
        $table = JTable::getInstance('MenuType');
        $result = $table->load(array('menutype' => $menuType));

        return $result;
    }

    protected static function createMenuType($menu)
    {
        $table = JTable::getInstance('MenuType');

        return $table->save($menu);
    }

    protected static function createMenuItems($menu, $items, $component)
    {
        $extension = JTable::getInstance('Extension');
        $componentId = $extension->find(array('type' => 'component', 'element' => $component));

        foreach ($items as $item) {
            self::createMenuItem($menu, $item, $componentId);
        }
    }

    protected static function createMenuItem($menu, $item, $componentId)
    {
        $defaults = array(
            'menutype'     => $menu['menutype'],
            'alias'        => JFilterOutput::stringURLSafe($item['title']),
            'type'         => 'component',
            'published'    => 1,
            'parent_id'    => 1,
            'level'        => 1,
            'component_id' => $componentId,
            'access'       => 1,
            'client_id'    => 0,
            'language'     => '*',
        );

        $data = array_merge($defaults, $item);
        $table = JTable::getInstance('Menu');

        $table->setLocation($data['parent_id'], 'last-child');

        return $table->save($data);
    }

    protected static function createMenuModule($menu, $module, $position = 'position-7')
    {
        $data = array(
            'title'     => $module['title'],
            'ordering'  => 0,
            'position'  => $position,
            'published' => 1,
            'module'    => 'mod_menu',
            'access'    => 1,
            'showtitle' => 1,
            'language'  => '*',
            'client_id' => 0,
            'params'    => '{"menutype":"' . $menu['menutype'] . '"}',
        );

        $table = JTable::getInstance('Module');

        if (!$table->save($data)) {
            return false;
        }

        $dbo = JFactory::getDbo();
        $dbo->setQuery('INSERT INTO `#__modules_menu` (moduleid, menuid) VALUES (' . $table->id . ', 0)');

        return $dbo->execute();
    }
}
