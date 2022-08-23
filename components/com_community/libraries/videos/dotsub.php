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
class CTableVideoDotsub extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);
			
		$pattern = '/dotsub.com\/view\/(.+)/i';
		if (preg_match($pattern, $this->url)) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
		}

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
        preg_match("/dotsub.com\/view\/(.+)/i", $this->url, $matches);
        return !empty( $matches[1] ) ? $matches[1] : '';
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'dotsub';
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
			$videoId = $this->videoId;
		}
		
        return '<iframe width="' . $videoWidth . '" height="' . $videoHeight . '" src="https://dotsub.com/media/' . $videoId . '/embed/" frameborder="0" allowfullscreen></iframe>';
	}

}
