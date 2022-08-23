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
 * Class to manipulate data from Ku6
 *
 * @access	public
 */
class CTableVideoKu6 extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://rbv01.ku6.com/wifi/' . $this->videoId;
	}

	public function isValid()
	{	
		if (!$this->videoId) {
			$this->videoId = $this->getId();
		}

		if ($this->videoId) {
			$this->url = str_replace('http:', 'https:', $this->url);
			
			$pattern = '/www\.ku6\.com\/video\/detail\?id(.*)/';
			if (preg_match($pattern, $this->url)) {
				$this->xmlContent = CRemoteHelper::getContent($this->url);

				return true;
			}
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

		$pattern    = '/flvURL: "(.*?)"/';
        preg_match( $pattern, $this->xmlContent, $matches);
        if (!empty($matches[1])) {
        	return $matches[1];
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
		return 'ku6';
	}

	public function getTitle()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern    = '/document\.title = "(.*?)"/';
        preg_match( $pattern, $this->xmlContent, $matches);
        
        return !empty($matches[1]) ? $matches[1] : '';
	}

	public function getDescription()
	{	
		return '';
	}

	public function getDuration()
	{	
        return 0;
	}

	public function getThumbnail()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/"poster": "(.*?)"/';
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

		$embedCode = '<iframe width="100%" height="'.$videoHeight.'" src="'.$videoId.'" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        return $embedCode;
	}
}
