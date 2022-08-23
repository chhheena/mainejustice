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

class mxYouTuber_Model_Data_Playlist{
    
    private $_id = null;
    private $_items = [];
    private $_nextPageToken = '';
    private $_dataModel = null;
    
    public function __construct($playlistID, $dataModel){
        $this->_id = $playlistID;
        $this->_dataModel = $dataModel;
    }
    
    public function getItems($start=0, $length=10, $exceptVideosIDs=null){
        $this->_items = [];
        $this->_nextPageToken = '';
        $needed = $start+$length;
        if(count($this->_items)<$needed && $this->canLoadMore()){
            for($i=0;$i<99;$i++){
                $this->loadMore($exceptVideosIDs);
                if(!$this->canLoadMore() || count($this->_items)>=$needed){
                    break;
                }
            }
            
        }
        if(count($this->_items)<$needed){
            $this->_nextPageToken = '';
        }
        if(count($this->_items)<=$start){
            return [];
        }
        
        return array_slice($this->_items, $start, $length);
    }
    
    public function haveMoreThan($amount){
        if(count($this->_items)>$amount || $this->canLoadMore()){
            return true;
        }
        return false;
    }
    
    public function canLoadMore(){
        if(count($this->_items)==0){
            return true;
        }
        return !empty($this->_nextPageToken);
    }
    
    public function loadMore($exceptVideosIDs){
        if(!is_array($exceptVideosIDs)){
            $exceptVideosIDs = [];
        }
        $params = array(
            'part' => 'snippet,status',
            'maxResults' => 50,
            'playlistId' => $this->_id
        );
        if (!empty($this->_nextPageToken)){
            $params['pageToken'] = $this->_nextPageToken;
        }
        
        $response = $this->_dataModel->getData('playlistItems', $params);
        
        if(!isset($response['items'][0])) {
            throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_PLAYLIST_ID_NOT_FOUND'), $this->_id));
        }
        
        if(!empty($response['nextPageToken'])){
            $this->_nextPageToken = $response['nextPageToken'];
        }
        else{
            $this->_nextPageToken = '';
        }
        
        $ids = array();
        foreach($response['items'] as $item){
            $ids[] = $item['snippet']['resourceId']['videoId'];
        }
        
        $videos = $this->_dataModel->getVideo($ids);
        
        foreach($videos as $item){
            if(isset($item['snippet']['thumbnails']['default']) && !in_array($item['id'], $exceptVideosIDs)){
                $this->_items[] = $item;
            }
        }
        
    }
    
    
}
