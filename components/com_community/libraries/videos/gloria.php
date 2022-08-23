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
class CTableVideoGloria extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'https://gloria.tv/video/' . $this->videoId;
	}

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);
		
		$url = "https://gloria.tv/oembed/?url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);
		
		if (isset($obj->provider_name) && $obj->provider_name == 'gloria.tv') {
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
		$pattern    = '/gloria.tv\/video\/?(.*)/';
        preg_match( $pattern, $this->url, $matches);

        return !empty($matches[1]) ? $matches[1] : '';
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'gloria';
	}

	public function getTitle()
	{	
		$url = "https://gloria.tv/oembed/?url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->title;
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
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/video:duration"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getThumbnail()
	{	
		$url = "https://gloria.tv/oembed/?url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->thumbnail_url;
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
        $url = "https://gloria.tv/oembed/?url=https://gloria.tv/video/".$videoId;
		$json = file_get_contents($url);
		$obj = json_decode($json);
        
        if (isset($obj->html)) {
			$embedCode = $obj->html;
        }
        
        return $embedCode;
	}
}
