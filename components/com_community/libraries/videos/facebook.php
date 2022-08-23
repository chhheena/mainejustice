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
class CTableVideoFacebook extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';
	var $data = false;

	public function getData() {
		if ($this->data) {
			return $this->data;
		}

		if (preg_match('/facebook\.com.*?videos.*?(\d+)/', $this->url, $match)
			|| preg_match('/facebook\.com.*?watch\/.*?v=(\d+)/', $this->url, $match)) {
			$videoId = $match[1];
		} else {
			return false;
		}

		$uri = "https://www.facebook.com/plugins/video.php?href=".urlencode($this->url)."&show_text=false";
		$options = new JRegistry;
		$options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');
		$headers = array(
			'Host' => 'www.facebook.com',
			'sec-fetch-user' => '?1'
		);
		try {
			$response = JHttpFactory::getHttp($options)->get($uri, $headers);
		} catch (Exception $e) {
			return false;
		}

		if ($response->code !== 200) {
			return false;
		}

		if (!preg_match('/<img.*?class=".*?img".*?src="(.*?)".*?height="(\d+)".*?width="(\d+)".*?\/>/', $response->body, $imgMatch)) {
			return false;
		}
		
		list($html, $thumb, $height, $width) = $imgMatch;

		$watchUrl = preg_quote("https://www.facebook.com/watch/?ref=external&amp;v=" . $videoId, "/");
		if (!preg_match('/<a href="'.$watchUrl.'" target="_blank" id=".*?">(.*?)<\/a>/', $response->body, $titleMatch)) {
			return false;
		}

		$title = $titleMatch[1];

		$data = new stdClass;
		$data->videoId = $videoId;
		$data->title = $title;
		$data->video_length = 0;
		$data->description = '';
		$data->thumbnail_url = htmlspecialchars_decode($thumb);
		$data->url = $this->url;
		$data->width = $width;
		$data->height = $height;

		$this->data = $data;
		$this->videoId = $data->videoId;
		$this->params = json_encode($data);

		return $this->data;
	}

	public function isValid()
	{	
		$this->url = str_replace('http:', 'https:', $this->url);

		$this->data = $this->getData();

		if (!$this->data) {
			return false;
		}

		return true;
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
        if (!$this->getData()) {
			return false;
		}

		return $this->videoId;
	}


	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'facebook';
	}

	public function getTitle()
	{
		if (!$this->getData()) {
			return false;
		}

		return $this->data->title;
	}

	public function getDescription()
	{	
		return '';
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
		if (!$this->getData()) {
			return false;
		}

		return $this->data->thumbnail_url;
	}

	public function getParams($videoid) {
		$db = JFactory::getDbo();
		$query = 'SELECT params FROM `#__community_videos` WHERE video_id = ' . $db->quote($videoid) . ' AND `status`="ready" AND `type`="facebook"';
		$db->setQuery($query);
		$result = $db->loadResult();
		return new JRegistry($result);
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
		
		$params = $this->getParams($videoId);
		$original_width = $params->get('width', 1);
		$original_height = $params->get('height', 1);

		$path = 'https://www.facebook.com/plugins/video.php?href=' . urlencode('https://www.facebook.com/watch?v=' . $videoId);
		$embedCode = '<iframe style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
		$embedCode .= '
<script>
	var popupIframe, videoIframe, videoWrapper, ratio, src, oWidth, oHeight, fWidth, fHeight, width, height, maxHeight;
	popupIframe = jQuery(\'.joms-popup__video\').find(\'iframe\');
	videoWrapper = jQuery(\'.video-player\');
	oWidth = '.$original_width.';
	oHeight = '.$original_height.';
	ratio = oWidth / oHeight;
	src = \''.$path.'\';

	if (popupIframe.length) {
		fWidth = ratio * popupIframe.height();

		if (fWidth > popupIframe.width()) {
			width = popupIframe.width();
		} else {
			width = fWidth;
		}

		popupIframe.attr(\'style\', \'width: \' + width + \'px !important;\');
		popupIframe.attr(\'src\', src);
	} else if(videoWrapper.length) {
		videoIframe = videoWrapper.find(\'iframe\');
		maxHeight = 500;
		fHeight = videoWrapper.width() / ratio;

		if (fHeight > maxHeight) {
			width = ratio * maxHeight;
		} else {
			width = ratio * fHeight;
		}

		height = width / ratio;

		videoIframe.attr(\'width\', width);
		videoIframe.attr(\'height\', height);
		videoIframe.attr(\'src\', src);
	}
</script>
';
		return $embedCode;
	}

}