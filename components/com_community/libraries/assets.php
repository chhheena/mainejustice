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

jimport('joomla.filesystem.file');

if (!class_exists('CAssets')) {

    /**
     * Global Asset manager
     */
    class CAssets
    {

        /**
         * Construct
         * @param type $name
         */
        protected function __construct($name = 'default')
        {
            $this->_init($name);
        }

        /**
         *
         * @staticvar CPath $instances
         * @param type $name
         * @return \CPath
         */
        public static function &getInstance($name = 'default')
        {
            static $instances;
            if (!isset($instances[$name])) {
                $instances[$name] = new CAssets();
            }
            return $instances[$name];
        }

        /**
         * Centralized location to attach asset to any page. It avoids duplicate
         * attachement
         * @staticvar boolean $added
         * @param type $path
         * @param type $type
         * @param type $assetPath
         * @return type
         */
        public function attach($path, $type, $assetPath = '')
        {
            $document = JFactory::getDocument();
            if ($document->getType() != 'html')
                return;

            if (!empty($assetPath)) {
                $path = $assetPath . $path;
            } else {
                $path = JURI::root(true) . '/components/com_community/' . CStringHelper::ltrim($path, '/');
            }

            $version = CFactory::getJomsVersion();
            $path .= "?$version";

            switch ($type) {
                case 'js':
                    $document->addScript($path);
                    break;
                case 'css':
                    //do not attach style.css if current direction is rtl (style.rtl is loaded from views/view)
                    if ($document->direction == 'rtl' && strpos($path, 'style.css') !== false) {
                        break;
                    }
                    $document->addStyleSheet($path);
                    break;
            }
        }

        /**
         * Init assets
         * @param type $name
         */
        public function _init($name)
        {
            $mainframe = JFactory::getApplication();
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();

            // Load JomSocial system-wide data.
            $mainframe->registerEvent('onBeforeCompileHead', array($this, '_loadData'));

            // Attach common variables.
            $this->addData('base_url', JURI::root());
            $this->addData('current_url', JURI::getInstance()->toString());
            $this->addData('assets_url', JURI::root(true) . '/components/com_community/assets/');
            $this->addData('script_url', JURI::root(true) . '/components/com_community/assets/_release/js/');
            $this->addData('client', ($mainframe->isClient('site') ? 'site' : 'admin'));
            $this->addData('my_id', CFactory::getUser()->id);

            $emoticons = CStringHelper::getEmoticonData();
            $this->addData('joms_emo', $emoticons);

            $enablereaction = !!$config->get('enablereaction');
            if ($enablereaction) {
                $this->addData('enablereaction', !!$config->get('enablereaction'));

                $reactionData = CStringHelper::getReactionData();
                $this->addData('joms_reaction', $reactionData);

                $tmpl = new CTemplate();
                $this->addLayout('stream.reaction', $tmpl->fetch('stream/reaction'));
            }

            if ($document->getType() == 'html') {
                // Deprecated.
                $document->addScriptDeclaration("joms_base_url = '" . JURI::root() . "';");
                $document->addScriptDeclaration("joms_assets_url = '" . JURI::root(true) . "/components/com_community/assets/';");
                $document->addScriptDeclaration("joms_script_url = '" . JURI::root(true) . "/components/com_community/assets/_release/js/';");

                // Language translation.
                $this->_loadLanguageTranslation();
                // Print IDs.
                $my = CFactory::getUser();
                $userid = JFactory::getApplication()->input->get('userid', '', 'INT');
                $user = CFactory::getUser($userid);
                $document->addScriptDeclaration('joms_my_id = ' . $my->id . ';');
                $document->addScriptDeclaration('joms_user_id = ' . $user->id . ';');
            }

            // Load default jQuery shipped with Joomla.
            JHtml::_('jquery.framework');
            JHtml::_('behavior.core');

            // Embedly card loader, included this everywhere if enabled.
            if ($config->get('enable_embedly')) {
                $document->addScript('//cdn.embedly.com/widgets/platform.js');
            }

            // Load FontAwesome icons pack.
            $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

            $assetFile = CFactory::getPath('assets://default.json');
            if ($assetFile) {
                $assets = json_decode(file_get_contents($assetFile));
                foreach ($assets->core->css as $css) {
                    $cssFile = CFactory::getPath('assets://' . $css . '.css');
                    if ($cssFile) {
                        $this->attach(basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                    }
                }
                foreach ($assets->core->js as $js) {
                    $jsFile = CFactory::getPath('assets://' . $js . '.js');
                    if ($jsFile) {
                        $this->attach(basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                    }
                }
            }

            if (JFactory::getApplication()->isClient('site')) {
                if ($config->get('enablepm')) {
                    $libs = JURI::root(true) . '/components/com_community/assets/vendors/libs.min.js';
                    $js = JURI::root(true) . '/components/com_community/assets/chat/chat.min.js';
                    JFactory::getDocument()->addScript($libs);
                    JFactory::getDocument()->addScript($js);
    
                    $template = new CTemplate();
    
                    $this->addData('chat_enablereadstatus', $config->get('enablereadstatus'));
                    $this->addData('chat_pooling_time_active', $config->get('message_pooling_time_active', 10));
                    $this->addData('chat_pooling_time_inactive', $config->get('message_pooling_time_inactive', 30));
                    $this->addData('chat_show_timestamp', $config->get('message_show_timestamp'));
                    $this->addData('chat_base_uri', rtrim( JUri::root() ));
                    $this->addData('chat_uri', CRoute::_('index.php?option=com_community&view=chat', false));
                    $this->addData('chat_time_format', $config->get('message_time_format'));
                    $this->addData('chat_template_notification_item', $template->fetch('chat/notification-item'));
                    $this->addData('chat_text_and', JText::_('COM_COMMUNITY_AND'));
                    $this->addData('chat_recall', $config->get('message_recall_minutes', 0));
                    $this->addData('message_sidebar_softlimit', (int) $config->get('message_sidebar_softlimit', 15));
                }

                /* Template init */
                $templateFile = CFactory::getPath('template://assets/' . $name . '.json');

                if ($templateFile) {
                    $assets = json_decode(file_get_contents($templateFile));
                    /* Load template core files */
                    foreach ($assets->core->css as $css) {
                        $cssFile = CFactory::getPath('template://assets/css/' . $css . '.css');
                        if ($cssFile) {
                            $this->attach(basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                        }
                    }
                    foreach ($assets->core->js as $js) {
                        $jsFile = CFactory::getPath('template://assets/js/' . $js . '.js');
                        if ($jsFile) {
                            $this->attach(basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                        }
                    }
                    /* Load template view files */
                    $view = JFactory::getApplication()->input->getWord('view');
                    if (isset($assets->views->$view)) {
                        if (isset($assets->views->$view->css)) {
                            foreach ($assets->views->$view->css as $css) {
                                $cssFile = CFactory::getPath('template://assets/css/view.' . $css . '.css');
                                if ($cssFile) {
                                    $this->attach('view.' . basename($css) . '.css', 'css', CPath::getInstance()->toUrl(dirname($cssFile)) . '/');
                                }
                            }
                        }
                    }
                    if (isset($assets->views->$view)) {
                        if (isset($assets->views->$view->js)) {
                            foreach ($assets->views->$view->js as $js) {
                                $jsFile = CFactory::getPath('template://assets/js/view.' . $js . '.js');
                                if ($jsFile) {
                                    $this->attach('view.' . basename($js) . '.js', 'js', CPath::getInstance()->toUrl(dirname($jsFile)) . '/');
                                }
                            }
                        }
                    }
                }
            }
        }

        protected function _loadLanguageTranslation()
        {
            $languages = array(
                'COM_COMMUNITY_PHOTO_DONE_TAGGING',
                'COM_COMMUNITY_SEARCH',
                'COM_COMMUNITY_NO_COMMENTS_YET',
                'COM_COMMUNITY_NO_LIKES_YET',
                'COM_COMMUNITY_SELECT_ALL',
                'COM_COMMUNITY_UNSELECT_ALL',
                'COM_COMMUNITY_SHOW_MORE',
                'COM_COMMUNITY_SHOW_LESS',
                'COM_COMMUNITY_FILES_LOAD_MORE',
                'COM_COMMUNITY_INVITE_LOAD_MORE',
                'COM_COMMUNITY_PRIVACY_PUBLIC',
                'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
                'COM_COMMUNITY_PRIVACY_FRIENDS',
                'COM_COMMUNITY_PRIVACY_ME',
                'COM_COMMUNITY_MOVE_TO_ANOTHER_ALBUM',
                'COM_COMMUNITY_POPUP_LOADING',
                'COM_COMMUNITY_CLOSE_BUTTON',
                'COM_COMMUNITY_SELECT_FILE',
                'COM_COMMUNITY_AUTHENTICATION_KEY',
                'COM_COMMUNITY_NEXT',
                'COM_COMMUNITY_SKIP_BUTTON',
                'COM_COMMUNITY_AUTHENTICATION_KEY_LABEL',
                'COM_COMMUNITY_NO_RESULT_FOUND',
                'COM_COMMUNITY_OF',
                'COM_COMMUNITY_EDITING_PAGE',
                'COM_COMMUNITY_CHANGE_PAGE_OWNER',
                'COM_COMMUNITY_EDITING_GROUP',
                'COM_COMMUNITY_CHANGE_GROUP_OWNER',
                'COM_COMMUNITY_CONFIGURATION_IMPORT_GROUPS',
                'COM_COMMUNITY_CONFIGURATION_IMPORT_USERS',
                'COM_COMMUNITY_EDITING_PHOTO',
                'COM_COMMUNITY_VIEW_PHOTO',
                'COM_COMMUNITY_EDITING_VIDEO',
                'COM_COMMUNITY_VIEW_VIDEO',
                'COM_COMMUNITY_SHOW_PREVIOUS_COMMENTS',
                'COM_COMMUNITY_FILES_DELETE_CONFIRM',
                'COM_COMMUNITY_MESSAGE',
                'COM_COMMUNITY_PENDING_INVITATION',
                'COM_COMMUNITY_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_COMMENT',
                'COM_COMMUNITY_CHAT_ARE_YOU_SURE_TO_LEAVE_THIS_CONVERSATION',
                'COM_COMMUNITY_CHAT_ARE_YOU_SURE_TO_DELETE_THIS_MESSAGE',
                'COM_COMMUNITY_CHAT_NAME_OF_CONVERSATION',
                'COM_COMMUNITY_CHAT_NAME_OF_CONVERSATION_SHOULD_NOT_BE_EMPTY',
                'COM_COMMUNITY_CHAT_NAME_OF_CONVERSATION_SHOULD_BE_LESS_THAN_250_CHARACTERS',
                'COM_COMMUNITY_APPS_LIST_ADDED',
                'COM_COMMUNITY_CANNOT_EDIT_COMMENT_ERROR',
                'COM_COMMUNITY_CLOSE_BUTTON_TITLE',
                "COM_COMMUNITY_EMOJI_SMILEYS_AND_PEOPLE",
                "COM_COMMUNITY_EMOJI_ANIMALS_AND_NATURE",
                "COM_COMMUNITY_EMOJI_FOOD_AND_DRINK",
                "COM_COMMUNITY_EMOJI_ACTIVITIES",
                "COM_COMMUNITY_EMOJI_TRAVEL_AND_PLACES",
                "COM_COMMUNITY_EMOJI_OBJECTS",
                "COM_COMMUNITY_EMOJI_SYMBOLS",
                "COM_COMMUNITY_EMOJI_FLAGS",
                "COM_COMMUNITY_EMOJI_SEARCH",
                "COM_COMMUNITY_EMOJI_SEARCH_RESULTS",
                "COM_COMMUNITY_EMOJI_RECENTLY_USED"
            );

            $translation = array();
            for ($i = 0; $i < count($languages); $i++) {
                $translation[$languages[$i]] = JText::_($languages[$i]);
            }

            // Rich editor translation.
            $translation['wysiwyg'] = array(
                'viewHTML' => JText::_('COM_COMMUNITY_EDITOR_VIEW_HTML'),
                'bold' => JText::_('COM_COMMUNITY_EDITOR_BOLD'),
                'italic' => JText::_('COM_COMMUNITY_EDITOR_ITALIC'),
                'underline' => JText::_('COM_COMMUNITY_EDITOR_UNDERLINE'),
                'orderedList' => JText::_('COM_COMMUNITY_EDITOR_ORDERED_LIST'),
                'unorderedList' => JText::_('COM_COMMUNITY_EDITOR_UNORDERED_LIST'),
                'link' => JText::_('COM_COMMUNITY_EDITOR_LINK'),
                'createLink' => JText::_('COM_COMMUNITY_EDITOR_INSERT_LINK'),
                'unlink' => JText::_('COM_COMMUNITY_EDITOR_REMOVE_LINK'),
                'image' => JText::_('COM_COMMUNITY_EDITOR_IMAGE'),
                'insertImage' => JText::_('COM_COMMUNITY_EDITOR_INSERT_IMAGE'),
                'description' => JText::_('COM_COMMUNITY_EDITOR_DESCRIPTION'),
                'title' => JText::_('COM_COMMUNITY_EDITOR_TITLE'),
                'text' => JText::_('COM_COMMUNITY_EDITOR_TEXT'),
                'submit' => JText::_('COM_COMMUNITY_CONFIRM'),
                'reset' => JText::_('COM_COMMUNITY_CANCEL'),
                'target' => JText::_('COM_COMMUNITY_EDITOR_TARGET'),
                'upload' => JText::_('COM_COMMUNITY_EDITOR_UPLOAD'),
                'file' => JText::_('COM_COMMUNITY_EDITOR_FILE'),
            );

            // Date translation.
            $translation['date'] = array(
                'days' => array(
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_1'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_2'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_3'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_4'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_5'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_6'),
                    JText::_('COM_COMMUNITY_DATEPICKER_DAY_7'),
                ),
                'months' => array(
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_1'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_2'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_3'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_4'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_5'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_6'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_7'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_8'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_9'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_10'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_11'),
                    JText::_('COM_COMMUNITY_DATEPICKER_MONTH_12')
                )
            );

            // Backward compatibility.
            // Any other translations should be added via `$this->addTranslations` function.
            $this->addData('translations', $translation);
        }

        /**
         * Attach a value as a jomsocial front-end data.
         * @param {string} $varname
         * @param {mixed} $value
         */
        public function addData($varname, $value)
        {
            if (!isset($this->_data)) {
                $this->_data = array();
            }
            $this->_data[$varname] = $value;
        }

        public function addLayout($name, $value)
        {
            if (!isset($this->_data)) {
                $this->_data = array();
            }

            if (!isset($this->_data['layouts'])) {
                $this->_data['layouts'] = array();
            }

            $this->_data['layouts'][$name] = $value;
        }

        /**
         * Attach translations to the jomsocial front-end data.
         * @param {array|string} $keys
         */
        public function addTranslations($keys)
        {
            if (!isset($this->_data)) {
                $this->_data = array();
            }
            if (!isset($this->_data['translations'])) {
                $this->_data['translations'] = array();
            }
            if (!is_array($keys)) {
                $keys = array($keys);
            }
            foreach ($keys as $key) {
                $this->_data['translations'][$key] = JText::_($key);
            }
        }

        /**
         * Load all jomsocial front-end data.
         */
        public function _loadData()
        {
            $document = JFactory::getDocument();

            if (!isset($this->_data)) {
                $this->_data = array();
            }
            if (!isset($this->_data['translations'])) {
                $this->_data['translations'] = new stdClass();
            }

            // Try to load data via `addScriptOptions` if possible so that it is available
            // when it needs to be used by other scripts.
            if (method_exists($document, 'addScriptOptions')) {
                $document->addScriptOptions('com_community', $this->_data);

                // If it failed, try a bit of hack to load data on the first script tag.
            } else if (isset($document->_scripts)) {
                $key  = '" type="text/template"></script><script>';
                $key .= 'joms_data = ' . json_encode($this->_data) . ';';
                $key .= '</script><script type="text/template" src="';
                $firstScript = array($key => array());
                $document->_scripts = $firstScript + $document->_scripts;
            }
        }
    }
}
