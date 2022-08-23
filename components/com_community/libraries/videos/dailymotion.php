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
 * Class to manipulate data from Daily Motion
 *
 * @access	public
 */
class CTableVideoDailymotion extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'https://api.dailymotion.com/video/'.$this->getId().'?fields=description,duration%2Cthumbnail_url%2Ctitle';

		//return 'http://www.dailymotion.com/video/'.$this->getId();
	}

	public function isValid()
	{	
		$url = "http://www.dailymotion.com/services/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);
		
		if (isset($obj->provider_name) && $obj->provider_name == 'Dailymotion') {
			$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
		}

		return false;
	}

	/**
	 * Extract DailyMotion video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{
        $pattern    = '/dailymotion.com\/?(.*)\/video\/(.*)/';
        preg_match( $pattern, $this->url, $match);

		$parts = explode('#', $match[2]);

        return !empty($match[2]) ? array_shift($parts) : null;
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'dailymotion';
	}

	public function getTitle()
	{
		$url = "http://www.dailymotion.com/services/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->title;
	}

	public function getDescription()
	{
		$url = "http://www.dailymotion.com/services/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->description;
	}

	public function getDuration()
	{
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/video:duration"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
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
		$url = "http://www.dailymotion.com/services/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->thumbnail_url;
	}

	/**
	 *
	 *
	 * @return $embedvideo specific embeded code to play the video
	 */
	public function getViewHTML($videoId, $videoWidth, $videoHeight)
	{
		if (!$videoId)
		{
			$videoId = $this->videoId;
		}

		$embedCode = '<iframe frameborder="0" width="'.$videoWidth.'" height="'.$videoHeight.'" src="http://www.dailymotion.com/embed/video/'.$videoId.'&autoPlay=1" allowfullscreen allow="autoplay"></iframe>';
        
        return $embedCode;
	}

}
