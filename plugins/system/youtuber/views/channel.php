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

class mxYouTuberView_channel extends mxYouTuberView_base {

    public function __construct($attribs, $controller) {
        parent::__construct($attribs, $controller);

        $this->setTemplate('channel');
        $dataModel = $this->getModel('data');

        if ($attribs['id'] == '' && $attribs['user'] != '') {
            $attribs['id'] = $dataModel->getChannelIDByUser($attribs['user']);
        }

        $this->channel = $dataModel->getChannel($attribs['id']);
        $this->playlists = array();
        $defaultPL_ID = '';
        $activePlaylists = array();
        if (isset($attribs['playlists']) && $attribs['playlists'] != '') {
            foreach (explode(',', $attribs['playlists']) as $plListID) {
                $plListID = trim($plListID);
                if ($plListID != '') {
                    $activePlaylists[] = $plListID;
                }
            }
        }

        if (count($activePlaylists)) {
            if (isset($this->channel['contentDetails']['relatedPlaylists']) && isset($this->channel['contentDetails']['relatedPlaylists']['uploads']) && in_array($this->channel['contentDetails']['relatedPlaylists']['uploads'], $activePlaylists)) {
                $this->playlists[$this->channel['contentDetails']['relatedPlaylists']['uploads']] = JText::_('PLG_SYSTEM_YOUTUBER_UPLOADS');
            }

            foreach ($dataModel->getPlaylists($attribs['id']) as $dPlaylist) {
                if (in_array($dPlaylist['id'], $activePlaylists)) {
                    $this->playlists[$dPlaylist['id']] = $dPlaylist['snippet']['title'];
                }
            }
        } else {
            if (isset($this->channel['contentDetails']['relatedPlaylists']) && isset($this->channel['contentDetails']['relatedPlaylists']['uploads'])) {
                $this->playlists[$this->channel['contentDetails']['relatedPlaylists']['uploads']] = JText::_('PLG_SYSTEM_YOUTUBER_UPLOADS');
            }

            foreach ($dataModel->getPlaylists($attribs['id']) as $dPlaylist) {
                $this->playlists[$dPlaylist['id']] = $dPlaylist['snippet']['title'];
            }
        }
        if (!isset($attribs['playlist_id']) || !isset($this->playlists[$attribs['playlist_id']])) {
            $plIDs = array_keys($this->playlists);
            $attribs['playlist_id'] = ($defaultPL_ID != '' ? $defaultPL_ID : reset($plIDs));
        }

        $this->playlist = $dataModel->getPlaylistModel($attribs['playlist_id']);
        $ids = array();
        $exIDs = [];
        if (!empty($attribs['except_videos'])) {
            $exIDs = explode(',', preg_replace('~\s~s', '', $attribs['except_videos']));
        }
        $start = $attribs['start'];
        $attribs['start']+= $attribs['limit'];
        $this->videos = $this->playlist->getItems($start, $attribs['limit'], $exIDs);
        $this->featVideo = array();
        $this->orderVideos($this->videos, $attribs['order_by'], $attribs['order_dir']);
        $attribs['rel'] = 'mxYouTubeR' . md5($attribs['id'] . $attribs['theme']);

        $this->featVideoAttribs = array_merge($attribs, array(
            'mode' => 'lightbox',
        ));

        if ($attribs['size'] == '') {
            $attribs['size'] = 'medium';
        }

        if ($this->featVideoAttribs['size'] == '') {
            $this->featVideoAttribs['size'] = 'default';
            if (isset($this->featVideo['snippet']['thumbnails']['medium']))
                $this->featVideoAttribs['size'] = 'medium';
            else if (isset($this->featVideo['snippet']['thumbnails']['maxres']))
                $this->featVideoAttribs['size'] = 'maxres';
            else if (isset($this->featVideo['snippet']['thumbnails']['standard']))
                $this->featVideoAttribs['size'] = 'standard';
        }



        $this->showHeader = strpos($attribs['display'], 'header') !== false;
        $this->showPlaylists = strpos($attribs['display'], 'playlists') !== false;
        $this->showTitle = strpos($attribs['display'], 'title') !== false;
        $this->showDescription = strpos($attribs['display'], 'description') !== false;
        $this->showMeta = strpos($attribs['display'], 'meta') !== false;
        $this->showDate = strpos($attribs['display'], 'date') !== false;

        $this->cfg = $attribs;
        unset($this->cfg['infinite_scroll']);

        $this->attribs = $attribs;
    }

}
