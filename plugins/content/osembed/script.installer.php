<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

require_once 'library/Installer/include.php';

use Alledia\Installer\AbstractScript;
use Alledia\Framework\Factory;
use Alledia\OSEmbed\Pro\Helper;

/**
 * Custom installer script
 */
class PlgContentOSEmbedInstallerScript extends AbstractScript
{
    /**
     * Method to run after an install/update method
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        parent::postFlight($type, $parent);

        $app = JFactory::getApplication();

        if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
            // Load Alledia Framework
            require_once JPATH_SITE . '/libraries/allediaframework/include.php';
        }

        $plugin = Factory::getExtension('osembed', 'plugin', 'content');
        $plugin->loadLibrary();

        // Check if at least one of the supported editors is installed
        $hasAnyEditor = false;
        /**
         *
          * TinyMCE
          */
        if (Helper::checkEditorIsInstalled('tinymce')) {
            $hasAnyEditor = true;

            if (!Helper::checkRequiredEditorSettings(array('name' => 'tinymce'), false)) {
                $app->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_TINYMCE_INVALID_SETTINGS'), 'warning');
            }
        }

        /**
         * JCE
         */
        if (Helper::checkEditorIsInstalled('jce')) {
            $hasAnyEditor = true;

            if (!defined('WF_ADMINISTRATOR')) {
                require JPATH_SITE . '/administrator/components/com_jce/includes/base.php';
            }

            if (!Helper::checkRequiredEditorSettings(array('name' => 'jce'), false)) {
                $app->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_JCE_INVALID_SETTINGS'), 'warning');
            }
        }

        // @todo: add a warning if there is no compatible editor

        // Removes deprecated update site entry
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
          ->delete('#__update_sites')
          ->where('location LIKE ' . $db->quote('%plg_system_osembed%'));
        $db->setQuery($query)->execute();
    }
}
