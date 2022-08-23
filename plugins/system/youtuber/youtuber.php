<?php

/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die(':)');

class plgSystemYoutuber extends JPlugin {

    protected $autoloadLanguage = true;
    private static $scriptsLoaded = false;
    private $_v = null;
    private $_static_content_version = '3.0.6';

    public function onAfterInitialise() {
        $app = JFactory::getApplication();
        $input = $app->input;
        $action = $input->get('action', '');
        if ($action != 'youtuber') {
            return true;
        }

        try {
            $this->_loadLang();
            parse_str(urldecode($input->getString('params', '')), $attribs);
            $attribs = $this->shortcodeAttribs($attribs);

            $view = $this->getView($attribs);

            $result = array(
                'success' => 1,
                'haveMore' => (isset($view->playlist) && $view->playlist->haveMoreThan($view->attribs['start'])),
                'html' => $view->render()
            );
        } catch (Exception $e) {
            $result = array(
                'error' => 'YouTubeR ' . JText::_('PLG_SYSTEM_YOUTUBER_ERROR') . ': ' . $e->getMessage()
            );
        }
        header('Content-Type: application/json', true);
        echo json_encode($result);
        $app->close();
    }

    public function onAfterRender() {
        $app = JFactory::getApplication();
        if ($this->_isAdmin()) {
            return;
        }
        $doc = JFactory::getDocument();
        if ($doc->getType() != 'html')
            return;
        $html = $this->_getResponseBody();
        $delim = utf8_strpos($html, '<body');
        if ($delim === false) {
            return true;
        }
        $htmlHead = utf8_substr($html, 0, $delim);
        $htmlBody = utf8_substr($html, $delim);

        if (strpos($htmlBody, '[mx_youtuber') === false) {
            return true;
        }
        $htmlBody = preg_replace_callback('~\[(mx_youtube[^ ]+)([^\]]+)]~', array($this, 'renderShortcode'), $htmlBody);

        $scriptsLoadMode = $this->params->get('scripts_load_mode', 'all');
        $loadFancybox = (int) $this->params->get('load_fancybox', 1);
        if ($scriptsLoadMode != 'all' && !self::$scriptsLoaded) {
            self::$scriptsLoaded = true;
            $mediaURI = JURI::root(true) . '/media/plg_system_youtuber/';
            $scriptsBody = array();

            if ($loadFancybox == 1) {
                $scriptsBody[] = '<script src="' . $mediaURI . 'assets/fancybox/jquery.fancybox.min.js?v=' . $this->_static_content_version . '"></script>';
            }
            $scriptsBody[] = '<script src="' . $mediaURI . 'assets/js/frontend.js?v=' . $this->_static_content_version . '"></script>';

            $htmlBody = preg_replace('~<\/body>~', implode("\n", $scriptsBody) . '</body>', $htmlBody, 1);
        }

        $this->_setResponseBody($htmlHead . $htmlBody);
        return true;
    }

    public function onBeforeCompileHead() {
        $app = JFactory::getApplication();
        $doc = JFactory::getDocument();
        if ($doc->getType() != 'html') {
            return;
        }
        $this->_loadLang();
        
        JHtml::_('jquery.framework');
        if (self::$scriptsLoaded) {
            return;
        }
        
        $mediaURI = JURI::root(true) . '/media/plg_system_youtuber/';

        if ($this->_isAdmin()) {
            if((int)$this->params->get('uploading_enable', 0)==1){
                $doc->addScript($mediaURI . 'assets/js/media-uploader.js?v=' . $this->_static_content_version);
                $doc->addScript($mediaURI . 'assets/js/mxyoutube.js?v=' . $this->_static_content_version);
                JHtml::script('https://apis.google.com/js/client.js');
                $doc->addStyleSheet($mediaURI . 'assets/css/backend.css?v=' . $this->_static_content_version);
                $doc->addScriptDeclaration('
                        jQuery(document).ready(function(){
                                if(typeof mxYouTubeRBtnClick !="function"){
                                    return;
                                }
                                if(window.wzYoutube==undefined){
                                    alert("' . JText::_('PLG_SYSTEM_YOUTUBER_ACTIVATE_PLUGIN') . '");
                                    jQuery(".mxYouTuberBtn").attr("onclick","return false;").css("opacity","0.5");
                                }
                                if("' . $this->params->get('googleOAuthKey') . '"==""){
                                        alert("' . JText::_('PLG_SYSTEM_YOUTUBER_SET_OAUTH_ID') . '");
                                        jQuery(".mxYouTuberBtn").attr("onclick","return false;").css("opacity","0.5");
                                }
                                else{
                                        window.wzYoutube.lang.authorize_account = "' . JText::_('PLG_SYSTEM_YOUTUBER_AUTHORIZE_YOUTUBE_ACCOUNT') . '";
                                        window.wzYoutube.lang.upload_video = "' . JText::_('PLG_SYSTEM_YOUTUBER_UPLOAD_VIDEO') . '";
                                        window.wzYoutube.lang.list_videos = "' . JText::_('PLG_SYSTEM_YOUTUBER_VIDEOS_LIST') . '";
                                        window.wzYoutube.lang.more_videos = "' . JText::_('PLG_SYSTEM_YOUTUBER_MORE_VIDEOS') . '";
                                        window.wzYoutube.lang.title = "' . JText::_('PLG_SYSTEM_YOUTUBER_TITLE') . '";
                                        window.wzYoutube.lang.video_title = "' . JText::_('PLG_SYSTEM_YOUTUBER_VIDEO_TITLE') . '";
                                        window.wzYoutube.lang.description = "' . JText::_('PLG_SYSTEM_YOUTUBER_DESCRIPTION') . '";
                                        window.wzYoutube.lang.video_description = "' . JText::_('PLG_SYSTEM_YOUTUBER_VIDEO_DESCRIPTION') . '";
                                        window.wzYoutube.lang.tags = "' . JText::_('PLG_SYSTEM_YOUTUBER_TAGS') . '";
                                        window.wzYoutube.lang.video_tags = "' . JText::_('PLG_SYSTEM_YOUTUBER_TAGS_SEPARATED_BY_COMMA') . '";
                                        window.wzYoutube.lang.privacy_status = "' . JText::_('PLG_SYSTEM_YOUTUBER_PRIVACY_STATUS') . '";
                                        window.wzYoutube.lang.upload = "' . JText::_('PLG_SYSTEM_YOUTUBER_UPLOAD') . '";
                                        window.wzYoutube.lang.privacy_public = "' . JText::_('PLG_SYSTEM_YOUTUBER_PUBLIC') . '";
                                        window.wzYoutube.lang.privacy_ulnisted = "' . JText::_('PLG_SYSTEM_YOUTUBER_UNLISTED') . '";
                                        window.wzYoutube.lang.privacy_private = "' . JText::_('PLG_SYSTEM_YOUTUBER_PRIVATE') . '";
                                        window.wzYoutube.lang.enter_video_title = "' . JText::_('PLG_SYSTEM_YOUTUBER_PLEASE_ENTER_VIDEO_TITLE') . '";
                                        window.wzYoutube.lang.choose_video_file = "' . JText::_('PLG_SYSTEM_YOUTUBER_PLEASE_CHOOSE_VIDEO_FILE') . '";
                                        window.wzYoutube.appID = "' . $this->params->get('googleOAuthKey') . '";
                                }
                        });
                ');
            }
        } 
        else{
            $jsVars = array(
                'ajax_url' => $this->_getAjaxURI(),
                'lang' => array(
                    'more' => JText::_('PLG_SYSTEM_YOUTUBER_MORE'),
                    'less' => JText::_('PLG_SYSTEM_YOUTUBER_LESS'),
                ),
                'fancybox_params' => $this->params->get('fancybox_params', '{"type":"iframe","iframe":{"allowfullscreen":true}}')
            );
            
            $doc->addCustomTag('<script type="application/json" id="youtuber-cfg">' . json_encode($jsVars) . '</script>');
            
            $doc->addStyleSheet($mediaURI . 'assets/fancybox/jquery.fancybox.min.css?v=' . $this->_static_content_version);
            $doc->addStyleSheet($mediaURI . 'assets/css/frontend.css?v=' . $this->_static_content_version);
            
            $loadFancybox = (int) $this->params->get('load_fancybox', 1);
            $scriptsLoadMode = $this->params->get('scripts_load_mode', 'all');
            
            if ($scriptsLoadMode == 'all' && !self::$scriptsLoaded) {
                self::$scriptsLoaded = true;
            
                if ($loadFancybox == 1) {
                    $doc->addScript($mediaURI . 'assets/fancybox/jquery.fancybox.min.js?v=' . $this->_static_content_version);
                    
                }
                if ((int) $this->params->get('load_gfont', 1)) {
                    JHtml::_('stylesheet', 'https://fonts.googleapis.com/css?family=Roboto:400,400italic,500,500italic,700,700italic&subset=latin,cyrillic');
                }

                $doc->addScript($mediaURI . 'assets/js/frontend.js?v=' . $this->_static_content_version);
            }
        }
    }

    public function getView($attribs) {
        $viewPath = JPATH_ROOT . '/plugins/system/youtuber/views/';
        $viewName = 'mxYouTuberView_' . $attribs['type'];
        $classFile = $viewPath . $attribs['type'] . '.php';
        if (is_file($classFile)) {
            require_once($viewPath . 'base.php');
            require_once($classFile);
            $view = new $viewName($attribs, $this);
            return $view;
        } else {
            throw new Exception('Incorrect shortcode attribute type "' . $attribs['type'] . '".');
        }
        return false;
    }

    public function shortcodeAttribs($rawAttribs) {
        $attribs = array();
        if (is_array($rawAttribs)) {
            $attribs = $rawAttribs;
        } else {
            preg_match_all('~([a-zA-Z0-9_\-]+)=\&quot;(.*?)(?=\&quot;)\&quot;~', $rawAttribs, $mchs);
            foreach ($mchs[1] as $k => $v) {
                $attribs[$v] = $mchs[2][$k];
            }
            preg_match_all('~([a-zA-Z0-9_\-]+)="([^"]+)"~', $rawAttribs, $mchs);
            foreach ($mchs[1] as $k => $v) {
                $attribs[$v] = $mchs[2][$k];
            }
        }
        $defaults = array(
            'type' => 'video',
            'id' => '',
            'videos' => '',
            'display' => 'header,title,date,channel,description,meta,header,playlists',
            'mode' => $this->params->get('mode'),
            'theme' => $this->params->get('theme'),
            'ytp_params' => '',
            'size' => '',
            'width' => '',
            'height' => '',
            'cols' => ((isset($attribs['type']) && $attribs['type'] == 'channel') ? 1 : (int) $this->params->get('cols')),
            'rows' => (int) $this->params->get('rows'),
            'responsive_limit' => $this->params->get('responsive_limit'),
            'max_words' => (int) $this->params->get('max_words'),
            'infinite_scroll' => 'false',
            'load_more' => 'true',
            'load_more_text' => JText::_('PLG_SYSTEM_YOUTUBER_LOAD_MORE'),
            'suggested_videos' => 'false',
            'order_by' => $this->params->get('order_by', 'default'),
            'order_dir' => $this->params->get('order_dir', 'asc'),
            'playlist_id' => '',
            'playlists' => '',
            'user' => '',
            'except_videos' => '',
            'title' => '',
            'description' => '',
            'start' => 0
        );
        $result = array_merge($defaults, $attribs);
        $result['limit'] = (int) $result['cols'] * (int) $result['rows'];
        return $result;
    }

    public function renderShortcode($matches) {
        $this->_loadLang();
        $shortCode = $matches[1];
        $attribs = $this->shortcodeAttribs($matches[2]);
        if ($shortCode == 'mx_youtuber_video') {
            $attribs['type'] = 'video';
        } else if ($shortCode == 'mx_youtuber_playlist') {
            $attribs['type'] = 'playlist';
        } else if ($shortCode == 'mx_youtuber_channel') {
            $attribs['type'] = 'channel';
        }
        try {
            $view = $this->getView($attribs);
            return $view->render();
        } catch (Exception $e) {
            if (JDEBUG || (int) $this->params->get('debug', 0) == 1) {
                return '<p><strong>YouTubeR ' . JText::_('PLG_SYSTEM_YOUTUBER_ERROR') . ':</strong> ' . $e->getMessage() . '</p>';
            } else
                return '<p><strong>YouTubeR ' . JText::_('PLG_SYSTEM_YOUTUBER_ERROR_NO_DEBUG') . '</strong>.</p>';
        }
        return '';
    }

    public function onExtensionAfterSave($context, $table, $isNew = true) {
        if ($context != 'com_plugins.plugin' || !isset($table->type) || !isset($table->element) || $table->type != 'plugin' || $table->element != 'youtuber') {
            return;
        }
        $params = new JRegistry($table->params);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
                ->update('#__update_sites')
                ->set('extra_query=' . $db->quote('purchase_code=' . $params->get('purchase_code', '')))
                ->where('name=' . $db->quote('YouTubeR'));
        $db->setQuery($query);
        $db->execute();
    }

    public function getTemplatePaths($theme = '') {
        $app = JFactory::getApplication();
        $mainThemePath = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/plg_system_youtuber/';
        $mediaPath = JPATH_ROOT . '/media/plg_system_youtuber/';
        $theme = ($theme != '' ? $theme : $this->params->get('theme'));
        $paths = array();
        $paths[] = $mainThemePath . $theme;
        $paths[] = $mainThemePath . 'default';
        $paths[] = $mediaPath . 'themes/' . $theme;
        $paths[] = $mediaPath . 'themes/default';
        return $paths;
    }

    private function _getAjaxURI() {
        switch ($this->params->get('ajax_url_mode', 'router')) {
            case 'relative':
                $ajaxURL = JUri::root(true) . '/index.php';
                break;
            case 'router':
                $ajaxURL = JRoute::_('index.php', false);
                break;
            case 'absolute':
            default:
                $ajaxURL = JUri::root() . 'index.php';
                break;
        }
        return $ajaxURL;
    }

    private function _loadLang() {
        $lang = JFactory::getLanguage();
        $lang->load('plg_system_youtuber', JPATH_ADMINISTRATOR);
    }

    private function _isAdmin() {
        $app = JFactory::getApplication();
        return ((method_exists($app, 'isAdmin') && $app->isAdmin()) || (method_exists($app, 'isClient') && $app->isClient('administrator')));
    }

    private function _setResponseBody($html) {
        if (version_compare($this->_getCoreVersion(), '4.0.0', 'ge')) {
            $app = JFactory::getApplication();
            return $app->setBody($html);
        }
        JResponse::setBody($html);
    }

    private function _getResponseBody() {
        if (version_compare($this->_getCoreVersion(), '4.0.0', 'ge')) {
            $app = JFactory::getApplication();
            return $app->getBody();
        }
        return JResponse::getBody();
    }

    private function _getCoreVersion() {
        if ($this->_v === null) {
            $this->_v = new JVersion;
        }
        $v = $this->_v->getShortVersion();
        if (strpos($v, '-') !== false) {
            $v = explode('-', $v);
            $v = $v[0];
        }
        return $v;
    }

}
