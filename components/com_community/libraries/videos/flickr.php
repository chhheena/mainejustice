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
 * Class to manipulate data from Flickr
 *
 * @access	public
 */
class CTableVideoFlickr extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';
	var $data = false;
	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://www.flickr.com/photos/'.$this->getId();
	}

	public function getData() {
		$url = "https://www.flickr.com/services/oembed/?format=json&url=".$this->url;
		$json = file_get_contents($url);
		return json_decode($json);
	}

	public function isValid()
	{	
		$this->data = $this->getData();

		if (isset($this->data->flickr_type)) {
			$this->url = $this->data->web_page;
			$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
		}

		return false;
	}

	/**
	 * Extract Flickr video id from the video url submitted by the user
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

        $pattern = '/https\:\/\/\w{3}\.?flickr.com\/photos\/(.*)/';
        preg_match($pattern, $this->data->web_page, $match);
       
        return !empty($match[1]) ? $match[1] : null ;
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'flickr';
	}

	public function getTitle()
	{
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
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
		return 0;
	}


	/**
	 *
	 * @param $videoId
	 * @return unknown_type
	 */
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
	public function getViewHTML($videoId, $videoWidth, $videoHeight)
	{
		if (!$videoId) {
			$videoId = $this->videoId;
		}

        $url = "https://www.flickr.com/services/oembed/?format=json&url=https://www.flickr.com/photos/".$videoId;
		$json = file_get_contents($url);
		$obj = json_decode($json);
        
        $palyerid = explode('_', $obj->thumbnail_url);
        $mp4_url = $obj->web_page.'play/site/'.$palyerid[1];
       
		$embedCode = '<video style="max-width:700px" width="100%" height="'.$videoHeight.'" controls><source src="'.$mp4_url.'" type="video/mp4"></video>';

        return $embedCode;
	}
}