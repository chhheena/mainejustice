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

class modRssFactoryCategoriesHelper
{
    public static function getTree()
    {
        $categories = JCategories::getInstance('RssFactory', array('mod_rssfactory'));
        $tree = self::getChildren($categories->get('root'));

        if (!$tree) {
            return '';
        }

        $html = array();

        //$html[] = '<a href="' . JRoute::_('index.php?option=com_rssfactory&view=category') . '">' . JText::_('Root') . '</a>';

        $html[] = '<ul id="treeview">';
        $level = $tree[0]['level'];

        foreach ($tree as $item) {
            if ($level > $item['level']) {
                $html[] = str_repeat('</ul>', $level - $item['level']) . '</li>';
            }

            if ($level < $item['level']) {
                $html[] = '<ul>';
            }

            $html[] = '<li><a href="' . JRoute::_('index.php?option=com_rssfactory&view=category&category_id=' . $item['id']) . '">' . $item['title'] . '</a>';
            $level = $item['level'];
        }

        $html[] = '</ul>';

        return implode("\n", $html);
    }

    protected static function getChildren($item)
    {
        static $tree = array();

        if (!$item) {
            return $tree;
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $tree[] = array(
                    'title' => $child->title,
                    'level' => $child->level,
                    'id'    => $child->id,
                );

                self::getChildren($child);
            }
        }

        return $tree;
    }
}
