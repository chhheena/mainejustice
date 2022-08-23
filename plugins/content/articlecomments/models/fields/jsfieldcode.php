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

    class JFormFieldJsFieldCode extends JFormField
    {

        protected $type = 'jsfieldcode';

        // getLabel() left out

        public function getInput()
        {
            // Check if JomSocial core file exists
            $corefile 	= JPATH_ROOT . '/components/com_community/libraries/core.php';

            jimport( 'joomla.filesystem.file' );
            if( !JFile::exists( $corefile ) )
            {
                return;
            }
            require_once( $corefile );
            /* Create the Application */
            $app = JFactory::getApplication('site');

            jimport( 'joomla.application.module.helper' );

            $db = JFactory::getDbo();

            $query = "SELECT id, alias AS fieldcode FROM ".$db->quoteName('#__categories')." WHERE published=".$db->quote('1')
                    ." AND ".$db->quoteName('extension')."=".$db->quote('com_content');
            $db->setQuery($query);
            $categories = $db->loadObjectList();

            if(!count($categories)){
                return;
            }

            $value = '';
            foreach($categories as $category){
                $selected = ( !empty($this->value) && in_array($category->id, $this->value)) ? 'selected': '' ;
                $value .= '<option '.$selected.' value="'.$category->id.'" >'.$category->fieldcode.'</option>';
            }

            return '<select multiple id="' . $this->id . '" name="' . $this->name . '">' .
            $value.
            '</select>';
        }
    }