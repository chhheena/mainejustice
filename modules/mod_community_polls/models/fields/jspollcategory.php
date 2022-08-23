<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldJspollcategory extends JFormField
{
    protected $type = 'jspollcategory';

    public function getInput()
    {
        // Check if JomSocial core file exists
        $corefile = JPATH_ROOT . '/components/com_community/libraries/core.php';

        jimport('joomla.filesystem.file');
        
        if (!JFile::exists($corefile)) {
            return;
        }

        require_once($corefile);

        /* Create the Application */
        $app = JFactory::getApplication('site');
        
        jimport('joomla.application.module.helper');

        $model = CFactory::getModel('polls');
        $pollCategories = $model->getAllCategories();

        $value = '<option id="0">'.JText::_('MOD_COMMUNITY_POLLS_ALL_CATEGORIES_SETTINGS').'</option>';

        foreach ($pollCategories as $category) {
            $selected = ($this->value == $category->id) ? 'selected': '' ;
            $value .= '<option '.$selected.' value="'.$category->id.'" >'.$category->name.'</option>';
        }

        return '<select id="' . $this->id . '" name="' . $this->name . '">' . $value . '</select>';
    }
}