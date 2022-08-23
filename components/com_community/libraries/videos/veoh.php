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
 * Class to manipulate data from Veoh
 *
 * @access	public
 */
class CTableVideoVeoh extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{  
		return 'http://www.veoh.com/watch/' . $this->videoId;
	}

	public function isValid()
	{	
		$pattern    = '/veoh.com\/watch\/?(.*)/';
        if (preg_match($pattern, $this->url)) {
        	$this->xmlContent = CRemoteHelper::getContent($this->url);

			return true;
        }

		return false;
	}

	/**
	 * Extract Veoh video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

		$pattern = '/og:url"\s+content="(.+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches);
        
        if (!empty($matches[1])) {
        	$id = explode('watch/', $matches[1]);
           
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
		return 'veoh';
	}

	public function getTitle()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

        $pattern = '/og:title"\s+content="(.+)"/i';
        preg_match($pattern, $this->xmlContent, $matches);

        return !empty($matches[1]) ? $matches[1] : '';
	}

	public function getDescription()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}

        $pattern = '/og:description"\s+content="(.+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDuration()
	{	
        if (!$this->xmlContent) {
            $this->xmlContent = CRemoteHelper::getContent($this->url);
        }

        $pattern = '/item-duration"\s+content="(.+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );

        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getThumbnail()
	{	
		if (!$this->xmlContent) {
			$this->xmlContent = CRemoteHelper::getContent($this->url);
		}


		$pattern = '/og:image"\s+content="(.+)"/i';
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
        
		$embedCode = '
		<object width="'.$videoWidth.'" height="'.$videoHeight.'">
			<embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1509&permalinkId='.$videoId.'&player=videodetailsembedded&videoAutoPlay=1&id=anonymous" 
				type="application/x-shockwave-flash" 
				allowscriptaccess="always" 
				allowfullscreen="true" 
				width="'.$videoWidth.'" 
				height="'.$videoHeight.'" 
				>
			</embed>
		</object>';
        return $embedCode;
	}
}
