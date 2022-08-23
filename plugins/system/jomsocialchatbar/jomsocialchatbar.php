<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class exists checking
 */
if (!class_exists('plgSystemjomsocialchatbar')) {

    class plgSystemjomsocialchatbar extends JPlugin {

        /**
         * Construct method
         * @param type $subject
         * @param type $config
         */
        public function __construct($subject, $config) {
            parent::__construct($subject, $config);
        }

        public function onBeforeRender() {
            $app = JFactory::getApplication();
            if ( ! $app->isClient('site') ) {
                return;
            }

            $input = $app->input;
            if ($input->get('tmpl') === 'component') {
                return;
            }

            $this->loadLanguage();
            
            // Load com_community core.
            require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

            $config = CFactory::getConfig();
            if(!$config->get('enablepm') || !$config->get('enablechatbar')) {
                return;
            }
            // Load com_community assets.
            $assets = CAssets::getInstance();

            $model = CFactory::getModel('chat');
            $model->getTotalNotifications(null);

            $document = JFactory::getDocument();

            JText::script('PLG_JOMSOCIALCHATBAR_ARE_YOU_SURE_TO_LEAVE_THIS_CONVERSATION');
            JText::script('PLG_JOMSOCIALCHATBAR_SEEN');
            JText::script('PLG_JOMSOCIALCHATBAR_AND');
            
            $path = JURI::root(true) . '/plugins/system/jomsocialchatbar/';

            // Load stylesheets.
            $document->addStyleSheet($path . 'assets/css/style.css');

            // Load scripts.
            $document->addScript($path . 'assets/js/app.min.js');

            // Variables.
            $vars = array(
                'configs' => array(
                    'chat_bar_position' => $this->params->get('chat_bar_position', 'right'),
                    'remember_last_state' => $this->params->get('remember_last_state', '1')
                ),
                'templates' => array(
                    'chatbar' => $this->getTemplate('chatbar'),
                    'chatbar_sidebar' => $this->getTemplate('chatbar-sidebar'),
                    'chatbar_window' => $this->getTemplate('chatbar-window'),
                    'chatbar_window_input' => $this->getTemplate('chatbar-window-input'),
                    'chatbar_window_search' => $this->getTemplate('chatbar-window-search')
                )
            );

            // Load variables.
            $document->addScriptDeclaration('joms_plg_jomsocialchatbar = ' . json_encode($vars) . ';');

            // Load chat configurations.
            $assets->addData('chat_pooling_time_active', $config->get('message_pooling_time_active', 10));
            $assets->addData('chat_pooling_time_inactive', $config->get('message_pooling_time_inactive', 30));
        }

        

        /**
         * Return content of a template file.
         * @param string $file
         * @return string
         */
        private function getTemplate($file) {
            $path = JPATH_ROOT . '/plugins/system/jomsocialchatbar/tmpl/';
            $template = '';

            $file = $path . $file . '.php';

            if ( JFile::exists($file) ) {
                ob_start();
                require($file);
                $template = ob_get_contents();
                ob_end_clean();

                // Reduce whitespaces.
                $template = preg_replace('/\n/', ' ', $template);
                $template = preg_replace('/\s\s+/', ' ', $template);
            }

            return $template;
        }

    }
}
