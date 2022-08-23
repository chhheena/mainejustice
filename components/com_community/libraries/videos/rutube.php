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
 * Class to manipulate data from Rutube
 *
 * @access	public
 */
class CTableVideoRutube extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://rutube.ru/play/embed/' . $this->videoId;
	}

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);
		
		$pattern    = '/rutube.ru\/video\/?(.*)/';
        if (preg_match($pattern, $this->url)) {
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
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:video"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches);

        if (!empty($matches[1])) {
        	$id = explode('embed/', $matches[1]);
        	return $id[1];
        } else {
        	return false;
        }
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'rutube';
	}

	public function getTitle()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches);
        
        return !empty($matches[1]) ? $matches[1] : '';
	}

	public function getDescription()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDuration()
	{	
        $this->url = str_replace('http:', 'https:', $this->url);

        if (!$this->xmlContent) {
            $this->xmlContent = CRemoteHelper::getContent($this->url);
        }

        $pattern = '/og:video:duration"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getThumbnail()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:image:url"\s+content="([^"]+)"/i';
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
        
        $embedCode = '<iframe width="100%" height="'.$videoHeight.'" src="https://rutube.ru/play/embed/'.$videoId.'" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        return $embedCode;
	}
}
