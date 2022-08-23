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
 * Class to manipulate data from Twitch
 *
 * @access	public
 */
class CTableVideoTwitch extends CVideoProvider
{
    var $xmlContent = null;
    var $url 		= '';
    var $videoId	= '';
    var $data;

    /**
     * Return feedUrl of the video
     */
    public function getFeedUrl()
    {
        return 'https://player.twitch.tv/?autoplay=false&video=' . $this->videoId;
    }

    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        if (preg_match('/www\.twitch\.tv\/videos\/(\d+)/', $this->url, $match)) {
            $videoId = $match[1];
            $type = 'video';
            $uri = 'https://api.twitch.tv/kraken/videos/' . $videoId;
        } else if (preg_match('/www\.twitch\.tv\/.*?\/clip\/([0-9a-zA-Z-]+)/', $this->url, $match)) {
            $videoId = $match[1];
            $type = 'clip';
            $uri = 'https://api.twitch.tv/kraken/clips/' . $videoId;
        } else {
            return false;
        }

        $config = CFactory::getConfig();
        $clientID = $config->get('twitchclientid');
        $options = new JRegistry;
        $options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');
        $headers = array(
            'Accept' => 'application/vnd.twitchtv.v5+json',
            'Client-ID' => $clientID,
        );

        try {
            $response = JHttpFactory::getHttp($options)->get($uri, $headers);
        } catch (\Throwable $th) {
            return false;
        }

        if ($response->code !== 200) {
            return false;
        }

        $result = new JRegistry($response->body);
        if (!$result->get('url')) {
            return false;
        }

        $data = $type === 'video' ? $this->parseVideoData($videoId, $result) : $this->parseClipData($videoId, $result);

        $this->data = $data;
        $this->videoId = $data->videoId;
        $this->params = json_encode($data);

        return $this->data;
    }

    protected function parseVideoData($videoId, $result)
    {
        $data = new stdClass;
        $data->videoId = $videoId;
        $data->title = $result->get('title', '');
        $data->video_length = $result->get('length', 0);
        $data->description = $result->get('description', '');
        $data->twitch_type = 'video';
        $data->thumbnail_url = $result->get('preview')->large;
        $data->url = $result->get('url');

        $resolutions = (array) $result->get('resolutions');
        $dimension = array_pop($resolutions);
        list($width, $height) = explode('x', $dimension);
        $data->width = $width;
        $data->height = $height;
        return $data;
    }

    protected function parseClipData($videoId, $result)
    {
        $data = new stdClass;
        $data->videoId = $videoId;
        $data->title = $result->get('title', '');
        $data->video_length = $result->get('duration', 0);
        $data->description = $result->get('description', '');
        $data->twitch_type = 'clip';
        $data->thumbnail_url = $result->get('thumbnails')->medium;
        $data->url = $result->get('url');

        $embed_html = $result->get('embed_html');
        preg_match("/width=['\"](\d+)['\"]/", $embed_html, $matches);
        $data->width = $matches[1];

        preg_match("/height=['\"](\d+)['\"]/", $embed_html, $matches);
        $data->height = $matches[1];
        return $data;
    }

    public function isValid()
    {
        if ($this->getId()) {
            return true;
        }

        return false;
    }

    /**
     *
     * @access	public
     * @param	video url
     * @return videoid
     */
    public function getId()
    {
        if (!$this->data) {
            $this->data = $this->getData();
        }

        if (!$this->data) {
            return false;
        }

        return $this->data->videoId . '|' . $this->data->twitch_type;
    }

    /**
     * Return the video provider's name
     *
     */
    public function getType()
    {
        return 'twitch';
    }

    public function getTitle()
    {
        $this->data = $this->getData();
        return !empty($this->data->title) ? $this->data->title : '';
    }

    public function getDescription()
    {
        $this->data = $this->getData();
        return !empty($this->data->description) ? $this->data->description : '';
    }

    public function getDuration()
    {
        $this->data = $this->getData();
        return !empty($this->data->video_length) ? $this->data->video_length : '';
    }

    public function getThumbnail()
    {
        $this->data = $this->getData();
        return !empty($this->data->thumbnail_url) ? $this->data->thumbnail_url : '';
    }

    /**
     *
     *
     * @return $embedvideo specific embeded code to play the video
     */
    public function getViewHTML($videoId, $videoWidth, $videoHeight)
    {
        if (!$videoId) {
            $videoId = $this->videoId;
        }
        $exploded = explode('|', $videoId);
        $id = $exploded[0];
        $type = $exploded[1];

        $host = JUri::getInstance()->getHost();
        if ($type === 'clip') {
            $path = 'https://clips.twitch.tv/embed?autoplay=true&clip=' . $id . '&parent=' . $host;
        } else {
            $path = 'https://player.twitch.tv/?autoplay=true&video=' . $id . '&parent=' . $host;
        }

        $embedCode = '<iframe width="700" height="' . $videoHeight . '" src="' . $path . '" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        return $embedCode;
    }
}
