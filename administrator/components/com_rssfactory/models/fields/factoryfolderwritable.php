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

class JFormFieldFactoryFolderWritable extends JFormField
{
    protected $type = 'FactoryFolderWritable';

    protected function getInput()
    {
        jimport('joomla.filesystem.folder');
        $folder = JPATH_COMPONENT_SITE . '/' . $this->element['folder'];
        $isWritable = intval(is_writable($folder));

        return FactoryTextRss::plural('field_factory_folder_writable_status', $isWritable, $folder);
    }
}
