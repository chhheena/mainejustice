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
 * Class to manipulate data from videa.hu
 *
 * @access	public
 */
class CTableVideoVidea extends CVideoProvider
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
		return 'https://videa.hu/player/v/' . $this->videoId;
	}

	public function getData() {
		if ($this->data) {
			return $this->data;
		}

		$url = "https://videa.hu/oembed/?format=json&url=".$this->url;
		$json = @file_get_contents($url);
		$data = json_decode($json);
		if (is_object($data)) {
			return $data;
		} else {
			return false;
		}
	}

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);
		$this->data = $this->getData();
		
		if ($this->data && isset($this->data->provider_name)) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
		}

		return false;
	}

	/**
	 * Extract Videa.hu video id from the video url submitted by the user
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

		$pattern = '/og:video:url"\s+content="(.+)"/i';
        preg_match($pattern, $this->xmlContent, $matches);

        $matches = explode('v/', $matches[1]);

        return !empty( $matches[1] ) ? str_replace('?autoplay=1', '', $matches[1]) : '';
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'videa';
	}

	public function getTitle()
	{	
		$this->data = $this->getData();
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
        if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/"duration":"(.*?)"/';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? (int) $matches[1] : 0;
	}

	public function getThumbnail()
	{	
		$this->data = $this->getData();

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
        
        $embedCode = '<iframe width="100%" height="'.$videoHeight.'" src="https://videa.hu/player?v='.$videoId.'&autoplay=1" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        
        return $embedCode;
	}
}
