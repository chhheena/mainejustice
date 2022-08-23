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
 * Class to manipulate data from vimeo video
 *
 * @access	public
 */
class CTableVideoVimeo extends CVideoProvider
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
		return 'http://vimeo.com/api/v2/video/' .$this->videoId.'.xml';
	}

	public function getData() {
		if ($this->data) {
			return $this->data;
		}

		$url =  'https://vimeo.com/api/oembed.json?url=' . $this->url;
		$json = json_decode(@file_get_contents($url));
		
		if (is_object($json)) {
			$this->data = $json;
			return $this->data;
		} else {
			return false;
		}
	}

	/*
	 * Return true if successfully connect to remote video provider
	 * and the video is valid
	 */
	public function isValid()
	{
		if ($this->getData()) {
			$params = new JRegistry();
			$params->set('width', $this->data->width);
			$params->set('height', $this->data->height);
			$this->params = $params->toString();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Extract Vimeo video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @returns videoid
	 */
	public function getId()
	{
	    if (!$this->getData()) {
			return '';
		}

        return $this->data->video_id;
	}

	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'vimeo';
	}

	public function getTitle()
	{
		if (!$this->getData()) {
			return '';
		}

		return $this->data->title;
	}

	/**
	 *
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getDescription()
	{
		if (!$this->getData()) {
			return '';
		}

		return $this->data->description;
	}

	public function getDuration()
	{
		if (!$this->getData()) {
			return '';
		}

		return $this->data->duration;
	}

	/**
	 *
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getThumbnail()
	{
		if (!$this->getData()) {
			return '';
		}

		return CVideosHelper::getIURL($this->data->thumbnail_url);
	}

	/**
	 *
	 *
	 * @return $embedCode specific embeded code to play the video
	 */
	public function getViewHTML($videoId, $videoWidth, $videoHeight)
	{
		if (!$videoId)
		{
			$videoId	= $this->videoId;
		}

		$embedCode = '<iframe src="//player.vimeo.com/video/' . $videoId . '?autoplay=1" width="' . $videoWidth . '" height="' . $videoHeight . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

        return $embedCode;
	}
}
