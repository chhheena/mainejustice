<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSEmbed\Pro;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use JFactory;
use JLog;
use JRegistry;
use JText;
use WFEditor;
use Exception;
use Alledia\OSEmbed\Free\Helper as FreeHelper;

jimport('joomla.log.log');


abstract class Helper extends FreeHelper
{
    /**
     * Check if the context matches the current url input
     *
     * @param  Array  $expectedContexts An array of arrays with the url variables
     * @return bool                     True, if matches the context
     */
    public static function checkContext(Array $expectedContexts)
    {
        $app = JFactory::getApplication();

        foreach ($expectedContexts as $context) {
            $match = 0;

            foreach ($context as $input => $expected) {
                if ($app->input->getCmd($input) === $expected) {
                    $match += 1;
                }
            }

            if ($match === count($context)) {
                return true;
            }
        }

        return false;
    }

    protected static function getEditorData()
    {
        $user   = JFactory::getUser();
        $params = new JRegistry($user->params);
        $editor = $params->get('editor');
        $usingGlobalEditor = false;

        if (empty($editor)) {
            // Check the editor set in the global configuration
            $config = JFactory::getConfig();
            $editor = $config->get('editor');
            $usingGlobalEditor = true;
        }

        return array(
            'name'   => $editor,
            'global' => $usingGlobalEditor,
            'user'   => $user
        );
    }

    public static function checkRequiredEditor()
    {
        $supportedEditors = array('tinymce', 'jce');

        // Check if the user has a custom editor
        $editorData = static::getEditorData();

        if (!in_array($editorData['name'], $supportedEditors)) {
            $log = 'OSEmbed requires one of the following editors: '
                    . implode(', ', $supportedEditors) . '. ';
            if (!$editorData['global']) {
                $log .= 'The user ' . $editorData['user'] . ' is using ' . $editorData['name'] . '. '
                    . 'OSEmbed preview will not work for it';
            } else {
                $log = 'The global editor setting is set to ' . $editorData['name'] . '. '
                    . 'OSEmbed preview will not work for users with the default editor';
            }

            JLog::add($log, JLog::WARNING, 'osembed.library');

            return false;
        }

        return true;
    }

    public static function checkEditorIsInstalled($editorName)
    {
        $editor = Factory::getExtension($editorName, 'plugin', 'editors');
        $id = $editor->getId();

        return !empty($id);
    }

    public static function checkRequiredEditorSettings($editorData = null, $addLog = true)
    {
        $app = JFactory::getApplication();

        if (empty($editorData)) {
            $editorData = static::getEditorData();
        }

        if ($editorData['name'] === 'tinymce') {
            $editorPlugin = Factory::getExtension('tinymce', 'plugin', 'editors');
            $params = $editorPlugin->params;
            $invalidElements = explode(',', $params->get('invalid_elements', ''));
            $invalidElements = array_map('trim', $invalidElements);

            if (in_array('iframe', $invalidElements)) {
                if ($addLog) {
                    JLog::add(
                        JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_TINYMCE_INVALID_SETTINGS_LOG'),
                        JLog::WARNING,
                        'osembed.library'
                    );
                    $app->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_TINYMCE_INVALID_SETTINGS'), 'warning');
                }

                return false;
            }
        } elseif ($editorData['name'] === 'jce') {
            if (!class_exists('WFEditor')) {
                require JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php';
                wfimport('editor.libraries.classes.editor');
            }

            $wf = WFEditor::getInstance();

            $extendedElements = explode(
                ',',
                preg_replace('#\s+#', '', $wf->getParam('editor.extended_elements', '', ''))
            );

            $valid = false;
            // Iframe shoud be an extended element, alone or with defined attributes: iframe, iframe[src|width]
            if (!empty($extendedElements)) {
                foreach ($extendedElements as $elem) {
                    if (substr_count($elem, 'iframe') > 0) {
                        $valid = true;
                        break;
                    }
                }
            }

            if (!$valid) {
                if ($addLog) {
                    JLog::add(
                        JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_JCE_INVALID_SETTINGS_LOG'),
                        JLog::WARNING,
                        'osembed.library'
                    );
                    $app->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_JCE_INVALID_SETTINGS'), 'warning');
                }
            }

            return $valid;
        }

        return true;
    }

    public static function complyBasicRequirementsForPreview()
    {
        // @todo: Add tests for requirements here, like: iframe tag.
        // Show warnings if the user has permission to change the required settings into the editor

        $complies = static::checkRequiredEditor();

        if ($complies) {
            $complies = static::checkRequiredEditorSettings();
        }

        return $complies;
    }

    public static function requestedFromSameDomain()
    {
        $myDomain       = @$_SERVER['SCRIPT_URI'];
        $requestsSource = @$_SERVER['HTTP_REFERER'];

        // Failback in case Apache/nginx do not provide the required information
        if (empty($myDomain) || empty($requestsSource)) {
            return true;
        }

        return parse_url($myDomain, PHP_URL_HOST) === parse_url($requestsSource, PHP_URL_HOST);
    }

    public static function detectParseHook()
    {
        $app = JFactory::getApplication();

        $contexts = array(
            array(
                'plg_task' => 'osembedpreview.parse_content'
            )
        );

        return Helper::checkContext($contexts);
    }
}
