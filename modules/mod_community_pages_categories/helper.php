<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die;

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

abstract class ModCommunityPagesCategoriesHelper
{   
	public static function getList(&$params)
	{
		$model =  CFactory::getModel('pages');
		$categories = $model->getCategories();

		$cTree = CCategoryHelper::getCategories($categories);

        $grouped = array();
        foreach ($cTree as $sub){
            $grouped[$sub['parent']][] = $sub;
        }

        $fnBuilder = function($siblings) use (&$fnBuilder, $grouped) {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling['id'];
                if(isset($grouped[$id])) {
                    $sibling['children'] = $fnBuilder($grouped[$id]);
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        $tree = $fnBuilder($grouped[0]);

        return $tree;
	}

    public static function doOutputList($TreeArray, $deep = 0, $params = array())
    {
        $padding = str_repeat('  ', $deep * 3);
        
        if ($deep == 0) {
            echo $padding . "<ul class='mod-pages-categories parent'>";
        } else {
            echo $padding . "<ul class='child level-" . $deep . "'>";
        }

        foreach ($TreeArray as $label => $arr) {
            echo $padding . "<li class='level-" . $deep . "'>";

            $link = '<a href="' . CRoute::_('index.php?option=com_community&view=pages&categoryid=' . $arr['id']) . '">' . $arr['name'];
            
            if ($params->get('numitems')) {
                $link .= ' (' . $arr['count'] . ')</a>';
            } else {
                $link .= '</a>';
            }

            echo $padding . $link;

            if ($params->get('show_description')) {
                echo '<div><small>' . $arr['description'] . '</small></div>';
            }

            if (isset($arr['children']) && $params->get('show_children')) {
                self::doOutputList($arr['children'], $deep + 1, $params);
            }
            
            echo $padding . "  </li>";
        }

        echo $padding . "</ul>";
    }
}
