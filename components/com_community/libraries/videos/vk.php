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
class CTableVideoVk extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'https://vk.com/' . $this->videoId;
	}

	public function isValid()
	{	
		// ignoring vk video
		return false;

		$this->url = str_replace('http:', 'https:', $this->url);
		
		$pattern = '/vk.com\/video/';
        if (preg_match($pattern, $this->url)) {
        	parse_str(parse_url($this->url, PHP_URL_QUERY), $video);

			if (isset($video['z'])) {
				$video = explode('/', $video['z']);
				if (isset($video[0]) && !empty($video[0])) {
					$this->url = 'https://vk.com/' . $video[0];
				}
			} 

        	$this->xmlContent = iconv('CP1251', 'UTF-8', CRemoteHelper::getContent($this->url));
        	
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
        parse_str(parse_url($this->url, PHP_URL_QUERY), $video);
		if (isset($video['z'])) {
			$video = explode('/', $video['z']);
			if (isset($video[0]) && !empty($video[0])) {
				return $video[0];
			}
		} else if (preg_match("/vk.com\/video-/", $this->url)) {
			$video = explode("vk.com/video-", $this->url);

			if (isset($video[1]) && !empty($video[1])) {
				return 'video-'.$video[1];
			}
		}

		return false;
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'vk';
	}

	public function getTitle()
	{	
		$pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDescription()
	{
		$pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDuration()
	{	
		$pattern = '/og:video:duration"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getThumbnail()
	{	
		$pattern = '/og:image"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
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
        $url = "https://vk.com/".$videoId;
        $this->xmlContent = iconv('CP1251', 'UTF-8', CRemoteHelper::getContent($url));
        
		$pattern = '/og:video"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );

        if (!empty($matches[1])) {
        	$embedvideo = $matches[1];
			$embedCode = '<iframe src="'.$embedvideo.'" width="' . $videoWidth . '" height="' . $videoHeight . '" allowfullscreen frameborder=0></iframe>';
        }

        return $embedCode;
	}
}
