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
class CTableVideoSapo extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'https://videos.sapo.pt/' . $this->videoId;
	}

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);
		$this->url = str_replace('#vhs-', '', $this->url);

		http://rd.videos.sapo.pt/J7MNZ7i7ESVGdRGw6cpQ

		$url = "https://rd3.videos.sapo.pt/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);
		
		if (isset($obj->provider_name) && $obj->provider_name == 'Sapo Videos') {
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
		$pattern    = '/videos.sapo.pt\/?(.*)/';
        preg_match( $pattern, $this->url, $matches);

        $this->url = 'https://videos.sapo.pt/' . $matches[1];

        return !empty($matches[1]) ? $matches[1] : '';
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'sapo';
	}

	public function getTitle()
	{	
		$url = "https://rd3.videos.sapo.pt/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->title;
	}

	public function getDescription()
	{
		$url = "https://rd3.videos.sapo.pt/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		$obj = json_decode($json);

		return $obj->synopse;
	}

	public function getDuration()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/duration"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getThumbnail()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/thumbnailUrl"\s+href="([^"]+)"/i';
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
        $url = "https://rd3.videos.sapo.pt/oembed?format=json&url=https://videos.sapo.pt/".$videoId;
		$json = file_get_contents($url);
		$obj = json_decode($json);
        
        if (isset($obj->html)) {
			$embedCode = $obj->html;
        }
        
        return $embedCode;
	}
}
