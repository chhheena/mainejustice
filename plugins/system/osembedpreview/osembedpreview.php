<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Alledia\OSEmbed\Pro\Embed;
use Alledia\OSEmbed\Pro\Helper;

include_once JPATH_SITE . '/plugins/content/osembed/include.php';

if (defined('OSEMBED_LOADED')) {
    /**
     * OSEmbed System Plugin
     *
     */
    class PlgSystemOSEmbedPreview extends AbstractPlugin
    {
        public $type = 'system';

        public function __construct(&$subject, $config = array())
        {
            $this->namespace = 'OSEmbedPreview';

            parent::__construct($subject, $config);

            Helper::addLog();
        }

        /**
         * @return void
         * @throws Exception
         */
        public function onAfterInitialise()
        {
            try {
                $this->init();

                // Check the minumum requirements
                if (!Helper::complyBasicRequirements(true)) {
                    return;
                }

                if (Helper::detectParseHook()) {
                    // Only allow requests from the same domain
                    if (!Helper::requestedFromSameDomain()) {
                        header('HTTP/1.0 403 Forbidden');
                        jexit('This plugin only accept requests from the same domain');

                        return;
                    }

                    $this->echoParsedContent();
                }

            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'osembed.library');
                Factory::getApplication()
                    ->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_EXCEPTION'), 'error');
            }
        }

        /**
         * @return void
         * @throws Exception
         */
        public function onBeforeRender()
        {
            if (!$this->isAllowedToRun()) {
                return;
            }

            try {
                // Instantiate the Preview feature
                if (Helper::complyBasicRequirementsForPreview() && $this->params->get('preview_media', true)) {
                    $versionUID = md5($this->extension->getVersion());
                    $options    = array(
                        'relative' => true,
                        'version'  => $versionUID
                    );

                    JHtml::_('stylesheet', 'plg_system_osembedpreview/admin.css', $options);
                    JHtml::_('script', 'plg_system_osembedpreview/bootbox.min.js', $options);
                    JHtml::_('script', 'plg_system_osembedpreview/preview.min.js', $options);
                    JFactory::getDocument()->addScriptDeclaration('
                        OSEmbedPreview.init(
                            {
                                juriRoot: "' . JURI::root() . '",
                                versionUID: "' . $versionUID . '",
                                ignoreTags: ' . json_encode(Embed::getIgnoreTags()) . '
                            }
                        );
                    ');
                }

            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'osembed.library');
                Factory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_OSEMBEDPREVIEW_EXCEPTION'), 'error');
            }
        }

        /**
         * @return bool
         */
        protected function isAllowedToRun()
        {
            if (!Helper::complyBasicRequirements(true)) {
                return false;
            }

            $contexts = array(
                'com_content_backend'   => array(
                    'option' => 'com_content',
                    'view'   => 'article',
                    'layout' => 'edit'
                ),
                'com_content_frontend'  => array(
                    'option' => 'com_content',
                    'view'   => 'form',
                    'layout' => 'edit'
                ),
                'com_k2'                => array(
                    'option' => 'com_k2',
                    'view'   => 'item',
                    'task'   => 'edit'
                ),
                'com_modules_backend'   => array(
                    'option' => 'com_modules',
                    'view'   => 'module',
                    'layout' => 'edit'
                ),
                'com_modules_frontend'  => array(
                    'option'     => 'com_config',
                    'controller' => 'config.display.modules'
                ),
                'parse_content_preview' => array(
                    'plg_task' => 'osembedpreview.parse_content'
                )
            );

            // Add trigger for event to extend/handle contexts
            JPluginHelper::importPlugin('osembed');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onOSEmbedGetValidContexts', array(&$contexts));

            // Check the current context
            return Helper::checkContext($contexts);
        }

        protected function echoJSON($result, $msg = '')
        {
            echo new JResponseJson($result, $msg);
            jexit();
        }

        protected function echoParsedContent()
        {
            $result = new stdClass;

            try {
                $content         = JFactory::getApplication()->input->get('content', null, 'raw');
                $result->content = Embed::parseContent($content, true);

            } catch (Exception $e) {
                $result = $e;
            }

            $this->echoJSON($result);
        }
    }

} else {
    $app = JFactory::getApplication();
    if ($app->isClient('administrator')) {
        $app->enqueueMessage(
            'OSEmbed Pro library wasn\'t loaded. Please reinstall or <a href="help@joomlashack.com">contact support</a>.',
            'warning'
        );
    }
}
