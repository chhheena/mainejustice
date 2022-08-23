<?php

/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die;

class mxYouTuberView_base {

    private $_tmpl = '';
    private $_controller;

    public function __construct($attribs, $controller) {
        $this->_controller = $controller;
    }

    protected function getModel($name) {
        static $models;
        if (!isset($models[$name])) {
            $modelsPath = JPATH_ROOT . '/plugins/system/youtuber/models/';
            $className = 'mxYouTuber_Model_' . ucfirst($name);
            $classFile = $modelsPath . $name . '.php';
            if (is_file($classFile)) {
                require_once($modelsPath . 'base.php');
                require_once($classFile);
                $models[$name] = new $className();
            } else {
                throw new Exception('Model not found "' . $name . '".');
            }
        }
        return $models[$name];
    }

    public function render() {
        $tmpl = $this->getTemplate();
        return $this->loadTemplate($tmpl);
    }

    public function loadTemplate($tmpl) {
        $theme = $this->attribs['theme'];
        $path = '';
        foreach ($this->_controller->getTemplatePaths($theme) as $tp) {
            if (is_file($tp . '/' . $tmpl . '.php')) {
                $path = $tp . '/' . $tmpl . '.php';
                break;
            }
        }
        if ($path == '') {
            throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_TMPL_FOR_THEME_NOT_FOUND'), $tmpl, $theme));
        }
        ob_start();
        include($path);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public static function staticVar($name, $value = null, $default = null) {
        static $vars;
        if (is_null($vars)) {
            $vars = array();
        }
        if (is_null($value)) {
            if (isset($vars[$name])) {
                return $vars[$name];
            } else if (!is_null($default)) {
                return $default;
            } else
                return null;
        }
        $vars[$name] = $value;
        return $value;
    }

    public function orderVideos(&$videos, $orderBy, $orderDir) {
        if ($orderBy != 'default') {
            self::staticVar('videosOrderBy', $orderBy);
            uasort($videos, array($this, '_sortVideos'));
        }
        if ($orderDir == 'desc') {
            $videos = array_reverse($videos);
        }
        return true;
    }

    public function _sortVideos($a, $b) {
        $orderBy = mxYouTuberView_base::staticVar("videosOrderBy");
        $varA = 0;
        $varB = 0;
        switch ($orderBy) {
            case "date":
                $varA = strtotime($a['snippet']['publishedAt']);
                $varB = strtotime($b['snippet']['publishedAt']);
                break;
            case "views":
                $varA = (int) @$a['statistics']['viewCount'];
                $varB = (int) @$b['statistics']['viewCount'];
                break;
            case "likes":
                $varA = (int) @$a['statistics']['likeCount'];
                $varB = (int) @$b['statistics']['likeCount'];
                break;
            case "comments":
                $varA = (int) @$a['statistics']['commentCount'];
                $varB = (int) @$b['statistics']['commentCount'];
                break;
        }
        if ($varA == $varB) {
            return 0;
        }
        return ($varA < $varB) ? -1 : 1;
    }

    public function getVideoHref($video, $attribs, $iframe = false) {
        $config = $this->getConfig();
        parse_str($attribs['ytp_params'], $ytPlayerAttribs);
        if ($attribs['suggested_videos'] == 'false') {
            $ytPlayerAttribs['rel'] = '0';
        }
        if ($attribs['mode'] == 'embed' && !isset($ytPlayerAttribs['autoplay'])) {
            $ytPlayerAttribs['autoplay'] = 0;
        } 
        else if (!isset($ytPlayerAttribs['autoplay'])){
            $ytPlayerAttribs['autoplay'] = 1;
        }
        if (!isset($ytPlayerAttribs['showinfo'])){
            $ytPlayerAttribs['showinfo'] = 0;
        }
        $id = $video['id'];
        if(isset($video['snippet']['resourceId']['videoId'])){
            $id = $video['snippet']['resourceId']['videoId'];
        }
        return 'https://www.youtube'.((int)$config->get('gdpr', 1)==1?'-nocookie':'').'.com/embed/' . $id . '?' . http_build_query($ytPlayerAttribs);
    }

    public function getVideoHTML($video, $attribs) {
        $size = $attribs['size'];
        if (isset($attribs['rel'])) {
            $attribs['rel'] = preg_replace('~[^a-z0-9]~i', '_', $attribs['rel']);
        }
        $thumbnail = $this->getVideoThumbnail($video, $size);
        $ratio = round($thumbnail['width'] / $thumbnail['height'], 2);
        switch ($attribs['mode']) {
            case 'embed':
                $html = '<div class="mxyt-video-frame ' . (empty($attribs['width']) && empty($attribs['height']) ? 'mxyt-size-default' : '') . '"><iframe width="' . $attribs['width'] . '" height="' . $attribs['height'] . '" src="' . $this->getVideoHref($video, $attribs, true) . '" frameborder="0" allowfullscreen></iframe></div>';
                break;
            case 'lightbox':
            case 'link':
            default:
                $html = '<a href="' . $this->getVideoHref($video, $attribs) . '" data-ratio="' . $ratio . '" class="mxyt-img-placeholder mxyt-videolink fancybox.iframe ' . ($attribs['mode'] == 'lightbox' ? ' mxyt-lightbox' : '') . '" ' . (isset($attribs['rel']) ? 'data-fancybox="' . $attribs['rel'] . '" data-fancybox-group="' . $attribs['rel'] . '"' : '') . ' data-fancybox-type="iframe" target="_blank">';
                $html .= '<img src="' . $this->getThumbURL($video, $size) . '" alt="' . htmlentities($video['snippet']['title'], ENT_QUOTES, 'UTF-8') . '" />';
                $html .= '<span class="mxyt-play">';
                $html .= '<i class="mxyt-icon mxyt-icon-play"></i>';
                $html .= '</span>';
                $html .= (isset($video['contentDetails']['duration']) ? '<span class="mxyt-time">' . $this->getVideoDuration($video['contentDetails']['duration']) . '</span>' : '');
                $html .= '</a>';
                break;
        }
        return $html;
    }

    public function getVideoThumbnail($video, $size) {
        if (isset($video['snippet']['thumbnails'][$size])) {
            $thumbnail = $video['snippet']['thumbnails'][$size];
        } else {
            $thumbnail = $video['snippet']['thumbnails']['default'];
        }
        return $thumbnail;
    }

    public function getThumbURL($video, $size) {
        return $this->getVideoThumbnail($video, $size)['url'];
    }

    public function getVideoDate($timestamp) {
        return date($this->_controller->params->get('date_format'), strtotime($timestamp));
    }

    public function getVideoDuration($str) {
        $int = new DateInterval($str);

        if ($int->h != 0) {
            $duration = $int->format('%h:%I:%S');
        } else {
            $duration = $int->format('%i:%S');
        }

        return $duration;
    }

    public function getLimitVideoDescr($text, $num_words) {
        $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
        $sep = ' ';
        if (count($words_array) > $num_words) {
            $text = implode($sep, array_slice($words_array, 0, $num_words)) . '...';
        }
        $text = preg_replace_callback('~([^\s]{24})([^\s])~is', array($this, '_breakWords_re_callback'), $text);
        return $text;
    }

    public function getFullVideoDescr($str) {
        $str = preg_replace_callback('~(https?://[^\s]+)~i', array($this, '_links_re_callback'), $str);
        return $str;
    }

    public function _links_re_callback($matches) {
        $title = (mb_strlen($matches[1], 'UTF-8') > 35 ? utf8_substr($matches[1], 0, 35) . '...' : $matches[1]);
        return '<a href="' . $matches[1] . '" target="_blank" rel="nofollow">' . $title . '</a>';
    }

    public function _breakWords_re_callback($match) {
        return $match[1] . ' ' . $match[2];
    }

    public function setTemplate($tmpl) {
        $this->_tmpl = $tmpl;
    }

    public function getTemplate() {
        return $this->_tmpl;
    }
    
    private function getConfig(){
        $plugin = JPluginHelper::getPlugin('system', 'youtuber');
        return new JRegistry($plugin->params);
    }

}
