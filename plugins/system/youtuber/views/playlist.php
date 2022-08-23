<?php
/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die;

class mxYouTuberView_playlist extends mxYouTuberView_base {

    public function __construct($attribs, $controller) {
        parent::__construct($attribs, $controller);

        $this->setTemplate('playlist');
        $dataModel = $this->getModel('data');
        $ids = array();
        
        if (isset($attribs['videos']) && trim($attribs['videos']) != '') {
            $tmp = explode(',', $attribs['videos']);
            foreach ($tmp as $tm) {
                $id = trim($tm);
                if ($id != '') {
                    $ids[] = $id;
                }
            }
            $this->videos = $dataModel->getVideo($ids);
        } else if ($attribs['id']) {
            $this->playlist = $dataModel->getPlaylistModel($attribs['id']);
            
            $exIDs = [];
            if (!empty($attribs['except_videos'])) {
                $exIDs = explode(',', preg_replace('~\s~s', '', $attribs['except_videos']));
            }
            $start = $attribs['start'];
            $attribs['start']+= $attribs['limit'];
            $this->videos = $this->playlist->getItems($start, $attribs['limit'], $exIDs);
        } else {
            throw new Exception(JText::_('PLG_SYSTEM_YOUTUBER_NO_PLAYLIST_AND_VIDEO_ID'));
        }
        
        $this->orderVideos($this->videos, $attribs['order_by'], $attribs['order_dir']);

        $attribs['rel'] = 'mxYouTubeR' . md5($attribs['id'] . $attribs['theme']);

        if ($attribs['size'] == '') {
            $attribs['size'] = 'medium';
        }

        $this->showTitle = strpos($attribs['display'], 'title') !== false;
        $this->showDate = strpos($attribs['display'], 'date') !== false;
        $this->showDescription = strpos($attribs['display'], 'description') !== false;
        $this->showMeta = strpos($attribs['display'], 'meta') !== false;

        $this->cfg = $attribs;
        unset($this->cfg['infinite_scroll']);

        $this->attribs = $attribs;
    }

}
