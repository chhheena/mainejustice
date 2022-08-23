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

require_once (COMMUNITY_COM_PATH.'/models/videos.php');

/**
 * Class to manipulate data from VK
 *
 * @access	public
 */
class CTableVideoFunnyordie extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';
	var $data		= false;

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://www.funnyordie.com/videos/' . $this->videoId;
	}

	public function getData() {
		$url = "http://www.funnyordie.com/oembed.json?url=".$this->url;
		$json = file_get_contents($url);
		return json_decode($json);
	}

	public function isValid()
	{	
		$http = new JHttp();
		$pattern = '/og:url"\s+content="([^"]+)"/i';
        preg_match($pattern, $http->get($this->url)->body, $matches);
        $this->url = $matches[1];

		$this->data = $this->getData();

		if (isset($this->data->provider_name) && $this->data->provider_name == 'Funny Or Die') {
			$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
		}
		
		return false;
	}

	/**
	 * Extract VK video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{	
        preg_match("/funnyordie.com\/videos\/(.+)/", $this->url, $matches);
        return !empty( $matches[1] ) ? $matches[1] : '';
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'funnyordie';
	}

	public function getTitle()
	{	
		if (!$this->data) {
			$this->data = $this->getData();
		}
		return $this->data->title;
	}

	public function getDescription()
	{
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDuration()
	{	
		if (!$this->data) {
			$this->data = $this->getData();
		}

		return $this->data->duration;
	}

	public function getThumbnail()
	{	
		if (!$this->data) {
			$this->data = $this->getData();
		}
		
		return $this->data->thumbnail_url;
	}

	/**
	 *
	 *
	 * @return $embedvideo specific embeded code to play the video
	 */
	public function getViewHTML( $videoId, $videoWidth, $videoHeight )
	{
		if (!$videoId) {
            $videoId = $this->videoId;
        }
        
        $embedCode = '';
        $url = "http://www.funnyordie.com/oembed.json?url=http://www.funnyordie.com/videos/".$videoId;
		$json = file_get_contents($url);
		$obj = json_decode($json);
        
        if (isset($obj->html)) {
			preg_match('/<iframe(.*?)<\/iframe>/', $obj->html, $matches);
			$embedCode = $matches[0];
        }
        
        return $embedCode;
	}
}
