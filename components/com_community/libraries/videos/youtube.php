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

require_once(COMMUNITY_COM_PATH . '/models/videos.php');

/**
 * Class to manipulate data from YouTube
 *
 * @access	public
 */
class CTableVideoYoutube extends CVideoProvider
{
    var $xmlContent = null;
    var $url = '';
    var $videoId = null;
    var $useApi = false;
    var $data;
    var $params;

    public function __construct($db = null)
    {
        $config = CFactory::getConfig();
        $this->useApi = !!$config->get('youtubeapi');

        parent::__construct();
    }

    /**
     * Return feedUrl of the video
     */
    public function getFeedUrl()
    {
        return 'https://www.youtube.com/watch?v=' . $this->getId();
    }

    protected function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        if (preg_match('/youtube\.com\/watch\?v=.+/', $this->url)) {
            $uri = JUri::getInstance($this->url);
            $videoId = $uri->getVar('v');
        } else if (preg_match('/youtu\.be\/.+/', $this->url)) {
            $uri = JUri::getInstance($this->url);
            $videoId = str_replace('/', '', $uri->getPath());
        } else {
            return false;
        }

        $config = CFactory::getConfig();
        $apiKey = $config->get('youtubekey');
        $options = new JRegistry;
        $options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');
        $apiUrl = "https://youtube.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$videoId&key=$apiKey";
        try {
            $response = JHttpFactory::getHttp($options)->get($apiUrl);
        } catch (\Throwable $th) {
            return false;
        }

        if ($response->code !== 200) {
            return false;
        }

        $result = new JRegistry($response->body);
        if (!$result->get('items')) {
            return false;
        }

        list($item) = $result->get('items');
        $data = new stdClass;
        $data->videoId = $item->id;
        $data->title = $item->snippet->title;
        $data->description = $item->snippet->description;
        $data->thumbnail = isset($item->snippet->thumbnails->standard) ? $item->snippet->thumbnails->standard->url : $item->snippet->thumbnails->high->url;

        $ISODuration = $item->contentDetails->duration;
        $interval = new DateInterval($ISODuration);
        $data->duration = ($interval->d * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;

        $this->data = $data;
        $this->videoId = $data->videoId;
        $this->params = json_encode($data);

        return $this->data;
    }

    /*
     * Return true if successfully connect to remote video provider
     * and the video is valid
     */
    public function isValid()
    {
        return $this->useApi ? $this->isValidApi() : $this->isValidLegacy();
    }

    protected function isValidApi()
    {
        if ($this->getIdApi()) {
            return true;
        }

        return false;
    }

    protected function isValidLegacy()
    {
        if (!parent::isValid()) {
            return false;
        }

        // Connect and get the remote video
        if ($this->xmlContent == 'Invalid id') {
            throw new Exception(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_ID_ERROR'));
            return false;
        }

        if ($this->xmlContent == 'Video not found') {
            throw new Exception(JText::_('COM_COMMUNITY_VIDEOS_YOUTUBE_ERROR'));
            return false;
        }

        return true;
    }

    /**
     * Extract YouTube video id from the video url submitted by the user
     *
     * @access	public
     * @param	video url
     * @return videoid
     */
    public function getId()
    {
        return $this->useApi ? $this->getIdApi() : $this->getIdLegacy();
    }

    protected function getIdApi()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->videoId;
    }

    protected function getIdLegacy()
    {
        if ($this->videoId) {
            return $this->videoId;
        }

        preg_match_all(
            '~
            # Match non-linked youtube URL in the wild. (Rev:20111012)
            https?://         # Required scheme. Either http or https.
            (?:[0-9A-Z-]+\.)? # Optional subdomain.
            (?:               # Group host alternatives.
              youtu\.be/      # Either youtu.be,
            | youtube\.com    # or youtube.com followed by
              \S*             # Allow anything up to VIDEO_ID,
              [^\w\-\s;]       # but char before ID is non-ID char.
            )                 # End host alternatives.
            ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
            (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
            (?!               # Assert URL is not pre-linked.
              [?=&+%\w]*      # Allow URL (query) remainder.
              (?:             # Group pre-linked alternatives.
                [\'"][^<>]*>  # Either inside a start tag,
              | </a>          # or inside <a> element text contents.
              )               # End recognized pre-linked alts.
            )                 # End negative lookahead assertion.
            [?=&+%\w]*        # Consume any URL (query) remainder.
            ~ix',
            $this->url,
            $matches
        );

        if (isset($matches) && !empty($matches[1])) {
            return $matches[1][0];
        }

        return false;
    }

    /**
     * Return the video provider's name
     *
     */
    public function getType()
    {
        return 'youtube';
    }

    public function getTitle()
    {
        return $this->useApi ? $this->getTitleApi() : $this->getTitleLegacy();
    }

    protected function getTitleApi()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->title;
    }

    protected function getTitleLegacy()
    {
        $pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match($pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
    }

    public function getDescription()
    {
        return $this->useApi ? $this->getDescriptionApi() : $this->getDescriptionLegacy();
    }

    protected function getDescriptionApi()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->description;
    }

    protected function getDescriptionLegacy()
    {
        $pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match($pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
    }

    public function getDuration()
    {
        return $this->useApi ? $this->getDurationApi() : $this->getDurationLegacy();
    }

    protected function getDurationApi()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->duration;
    }

    protected function getDurationLegacy()
    {
        $pattern = '/itemprop="duration"\s+content="PT(\d+)M(\d+)S"/i';
        preg_match($pattern, $this->xmlContent, $matches);

        if (isset($matches[1]) && isset($matches[2])) {
            return ((int) $matches[1]) * 60 + ((int) $matches[2]);
        }

        return '';
    }

    /**
     * Get video's thumbnail URL from videoid
     *
     * @access 	public
     * @param 	videoid
     * @return url
     */
    public function getThumbnail()
    {
        return $this->useApi ? $this->getThumbnailApi() : $this->getThumbnailLegacy();
    }
    
    protected function getThumbnailApi()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->thumbnail;
    }
    
    protected function getThumbnailLegacy()
    {
        return CVideosHelper::getIURL('https://img.youtube.com/vi/' . $this->getId() . '/hqdefault.jpg');
    }

    /**
     *
     *
     * @return $embedvideo specific embeded code to play the video
     */
    public function getViewHTML($videoId, $videoWidth, $videoHeight, $data = array())
    {
        $html  = '<div class="joms-media--video joms-js--video" data-id="' . $videoId . '" data-type="youtube" data-path="' . htmlspecialchars($data['path']) . '">';
        $html .= '<img src="' . $data['thumbnail'] . '">';
        $html .= '<a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play"><div class="mejs-overlay-button"></div></a>';
        $html .= '</div>';

        return $html;
    }
}
