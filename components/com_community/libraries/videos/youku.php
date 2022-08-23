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
 * Class to manipulate data from Youku
 *
 * @access	public
 */
class CTableVideoYouku extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';
	
	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{	
		return 'https://v.youku.com/v_show/id_' . $this->videoId . '.html';
	}

	/*
	 * Return true if successfully connect to remote video provider
	 * and the video is valid
	 */
	public function isValid()
	{
		$this->url = str_replace('http:', 'https:', $this->url);
		
		$pattern    = '/v.youku.com\/v_show\/id_/';
        if (preg_match($pattern, $this->url)) {
        	$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
        }

		return false;
	}

	/**
	 * Extract youku video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		$pattern = '/https\:\/\/v.youku.com\/v_show\/id_(.*).html/';
        preg_match($pattern, $this->url, $matches);
        
        return !empty($matches[1]) ? $matches[1] : null;
	}

	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'youku';
	}

	public function getTitle()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/<title>(.*?)<\/title>/';
		preg_match( $pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
	}

	/**
	 * Get video's description from videoid
	 *
	 * @access 	public
	 * @param 	videoid
	 * @return desctiption
	 */
	public function getDescription()
	{
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/<meta name="description" content="(.*?)" \/>/';
		preg_match( $pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
	}

	/**
	 * Get video duration
	 *
	 * @return $duration seconds
	 */
	public function getDuration()
	{
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/seconds: \'(.*?)\'/';
		preg_match( $pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? (int) $matches[1] : 0;
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
		$this->url = str_replace('http:', 'https:', $this->url);
		$videoId = str_replace('=', '', $this->getId());

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/"encodevid":"'.$videoId.'".*?"img":"(.*?)"/';
		preg_match( $pattern, $this->xmlContent, $matches);
        return !empty($matches[1]) ? 'http:' . $matches[1] : '';
	}

	/**
	 *
	 *
	 * @return $embedvideo specific embeded code to play the video
	 */
	public function getViewHTML( $videoId, $videoWidth, $videoHeight )
	{
		if (!$videoId)
		{
			$videoId = $this->videoId;
		}

		$embedCode = '<iframe width="100%" height="'.$videoHeight.'" src="http://player.youku.com/embed/'.$videoId.'" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        
        return $embedCode;
	}
}
