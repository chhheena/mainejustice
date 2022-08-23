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

class JFormFieldFactoryPhpSettings extends JFormField
{
    protected $type = 'FactoryPhpSettings';

    protected function getInput()
    {
        $output = $this->getOutput($this->element['option']);

        return $output;
    }

    protected function getLabel()
    {
        if ('false' == $this->element['hasLabel']) {
            return '';
        }

        return parent::getLabel();
    }

    protected function getOutput($option)
    {
        $output = array();

        switch ($option) {
            case 'version':
                $output[] = '<img src="' . $_SERVER['PHP_SELF'] . '?=' . (function_exists('php_logo_guid') ? php_logo_guid() : '') . '" alt="PHP Logo !" />';
                $output[] = '<br />';
                $output[] = php_uname();
                break;

            case 'display_errors':
                $output[] = JText::_(ini_get('display_errors') ? 'JYES' : 'JNO');
                break;

            case 'file_uploads':
                $max_upload = intval(ini_get('upload_max_filesize'));
                $max_post = intval(ini_get('post_max_size'));
                $memory_limit = intval(ini_get('memory_limit'));

                $output[] = min($max_upload, $max_post, $memory_limit) . 'MB';
                break;

            case 'curl_support':
                $output[] = JText::_(function_exists('curl_init') ? 'JYES' : 'JNO');
                break;

            case 'gmt_time':
                $output[] = gmdate('Y-m-d H:i:s');
                break;
        }

        return implode("\n", $output);
    }
}
