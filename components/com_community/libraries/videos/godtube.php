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
 * Class to manipulate data from God Tube
 *
 * @access	public
 */
class CTableVideoGodtube extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';
	var $data = false;
	var $id = '';

	public function isValid()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}
		
		if ($this->getId()) {
			$isValidateUrl = preg_match("/www\.godtube\.com\/watch\/\?v=+/", $this->url);
			$isGodTubeVideo = $this->isGodTubeVideo();
			$isYoutubeEmbed = $this->isYoutubeEmbed();

			if ($isValidateUrl && ( $isGodTubeVideo || $isYoutubeEmbed )) {
				return true;
			}
		}

		throw new Exception(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_ID_ERROR'));
		return false;
	}

	/**
	 * Extract video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{	
		$exploded = explode('?v=', $this->url);
		if (count($exploded) === 2) {
			$type = '';
			$embedID = '';
			if ($embedID = $this->isGodTubeVideo()) {
				$type = 'godtube';
			} else if ($embedID = $this->isYoutubeEmbed()) {
				$type = 'youtube';
			} else {
				return false;
			}

			return $exploded[1] . '|' . $type . '|' . $embedID;
		}
		return false;
	}

	public function isGodTubeVideo() {
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		if (preg_match("/file: '(.*?).mp4'/", $this->xmlContent)) {
			return true;
		} else {
			return false;
		}
	}

	public function isYoutubeEmbed() {
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		preg_match("/file: 'https:\/\/www\.youtube\.com\/watch\?v=(.*?)'/", $this->xmlContent, $matches);
		if (count($matches) > 1) {
			return $matches[1];
		} else {
			return '';
		}
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'godtube';
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
	 * Get video's thumbnail URL from videoid
	 *
	 * @access 	public
	 * @param 	videoid
	 * @return url
	 */
	public function getThumbnail()
	{
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:image"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
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
			$videoId = $this->getId();
		}

		$embedCode = '';
		$exploded = explode('|', $videoId);
		
		if ($exploded[1] === 'godtube') {
			$embedCode = '<script type="text/javascript" src="https://www.godtube.com/embed/source/'.strtolower($exploded[0]).'.js?w=728&h=408&ap=true&sl=true"></script>';
			
		} else if($exploded[1] === 'youtube') {
			$embedCode = '<iframe width="728" height="400" src="https://www.youtube.com/embed/'.$exploded[2].'?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
		}
        
        return $embedCode;
	}

}
