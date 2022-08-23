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
class CTableVideoSoundcloud extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';
	var $data = false;

	public function getData() {
		$this->url = str_replace('http:', 'https:', $this->url);
		$url = "https://soundcloud.com/oembed?format=json&url=".$this->url;
		$json = file_get_contents($url);
		return json_decode($json);
	}

	public function isValid()
	{	
		if (!$this->data) {
			$this->data = $this->getData();
		}

		if (isset($this->data->provider_name) && $this->data->provider_name == 'SoundCloud') {
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
        if (!$this->data) {
			$this->data = $this->getData();
		}

		preg_match('/"https:\/\/w.soundcloud.com\/player\/\?visual=true&url=(.*?)"/', $this->data->html, $matches);
		
		$track = urldecode($matches[1]);
		$track = str_replace('https://api.soundcloud.com/tracks/', '', $track);
		$track = str_replace('&show_artwork=true', '', $track);

		return (int) $track;
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'soundcloud';
	}

	public function getTitle()
	{
		if (!$this->data) {
			$this->data = $this->getData();
		}

		return $this->data->title;
	}

	public function getDescription()
	{	
		if (!$this->data) {
			$this->data = $this->getData();
		}
		return $this->data->description;
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
		if (!$this->data) {
			$this->data = $this->getData();
		}
		return $this->data->thumbnail_url;
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
		
		$embedCode = '<iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/'.$videoId.'&color=%23ff5500&auto_play=true&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>';
        
        return $embedCode;
	}

}
